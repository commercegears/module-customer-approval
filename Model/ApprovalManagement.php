<?php
/**
 * Softaware Customer Approval
 *
 * @copyright Copyright (c) Softaware Commerce (https://www.softawarecommerce.co.uk/)
 */
declare(strict_types=1);

namespace Softaware\CustomerApproval\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\SessionCleanerInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Psr\Log\LoggerInterface;
use Softaware\CustomerApproval\Api\ApprovalManagementInterface;
use Softaware\CustomerApproval\Model\ResourceModel\StatusStorage;

class ApprovalManagement implements ApprovalManagementInterface
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly CustomerRegistry $customerRegistry,
        private readonly StatusStorage $storage,
        private readonly Notifier $notifier,
        private readonly PendingCounter $pendingCounter,
        private readonly IndexerRegistry $indexerRegistry,
        private readonly SessionCleanerInterface $sessionCleaner,
        private readonly CustomerTokenServiceInterface $tokenService,
        private readonly EventManager $eventManager,
        private readonly LoggerInterface $logger,
        private readonly ResourceConnection $resource
    ) {
    }

    public function getStatus(int $customerId): string
    {
        $this->customerRepository->getById($customerId);
        return $this->storage->getStatus($customerId) ?? self::STATUS_APPROVED;
    }

    public function approve(int $customerId, bool $notify = true): bool
    {
        $customer = $this->customerRepository->getById($customerId);
        $previous = $this->storage->getStatus($customerId);
        $this->storage->save($customerId, self::STATUS_APPROVED);
        $this->afterChange($customer, $previous, self::STATUS_APPROVED, null);
        if ($notify && $previous !== self::STATUS_APPROVED) {
            $this->notifier->notifyApproved($customer);
        }
        return true;
    }

    public function reject(int $customerId, ?string $reason = null, bool $notify = true): bool
    {
        $customer = $this->customerRepository->getById($customerId);
        $previous = $this->storage->getStatus($customerId);
        $this->storage->save($customerId, self::STATUS_REJECTED, $reason);
        $this->signOut($customerId);
        $this->afterChange($customer, $previous, self::STATUS_REJECTED, $reason);
        if ($notify && $previous !== self::STATUS_REJECTED) {
            $this->notifier->notifyRejected($customer, $reason);
        }
        return true;
    }

    public function setPending(int $customerId): bool
    {
        $customer = $this->customerRepository->getById($customerId);
        $previous = $this->storage->getStatus($customerId);
        $this->storage->save($customerId, self::STATUS_PENDING);
        $this->signOut($customerId);
        $this->afterChange($customer, $previous, self::STATUS_PENDING, null);
        return true;
    }

    /**
     * Ends storefront sessions and revokes API tokens, so a rejected customer is signed out everywhere.
     */
    private function signOut(int $customerId): void
    {
        try {
            $this->sessionCleaner->clearFor($customerId);
        } catch (\Throwable $e) {
            $this->logger->warning('Softaware_CustomerApproval: could not clear sessions: ' . $e->getMessage());
        }
        try {
            $this->tokenService->revokeCustomerAccessToken($customerId);
        } catch (\Throwable) {
            // No token issued.
        }
        try {
            // "Remember Me" (Magento_Persistent): without its row the persistent cookie no longer restores the customer.
            $connection = $this->resource->getConnection();
            $table = $this->resource->getTableName('persistent_session');
            if ($connection->isTableExists($table)) {
                $connection->delete($table, ['customer_id = ?' => $customerId]);
            }
        } catch (\Throwable $e) {
            $this->logger->warning('Softaware_CustomerApproval: could not clear persistent sessions: ' . $e->getMessage());
        }
    }

    private function afterChange(CustomerInterface $customer, ?string $previous, string $status, ?string $reason): void
    {
        $customerId = (int)$customer->getId();
        $this->customerRegistry->remove($customerId);
        $this->pendingCounter->invalidate();
        try {
            $this->indexerRegistry->get(Customer::CUSTOMER_GRID_INDEXER_ID)->reindexRow($customerId);
        } catch (\Throwable $e) {
            $this->logger->warning('Softaware_CustomerApproval: customer grid row not reindexed: ' . $e->getMessage());
        }
        $this->eventManager->dispatch('softaware_customer_approval_status_changed', [
            'customer' => $customer,
            'previous_status' => $previous,
            'status' => $status,
            'reason' => $reason,
        ]);
    }
}
