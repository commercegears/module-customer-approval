<?php
/**
 * Softaware Customer Approval
 *
 * @copyright Copyright (c) Softaware Commerce (https://www.softawarecommerce.co.uk/)
 */
declare(strict_types=1);

namespace Softaware\CustomerApproval\Model;

use Magento\Store\Model\StoreManagerInterface;
use Softaware\CustomerApproval\Api\ApprovalManagementInterface as Approval;
use Softaware\CustomerApproval\Exception\NotApprovedException;
use Softaware\CustomerApproval\Model\ResourceModel\StatusStorage;

/**
 * Decides whether a customer may sign in. Customers without a stored status (created before the module was
 * installed, imported, ...) count as approved.
 */
class LoginGuard
{
    public function __construct(
        private readonly Config $config,
        private readonly StatusStorage $storage,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    public function getBlockingStatus(int $customerId, ?int $websiteId): ?string
    {
        if (!$customerId || !$this->config->isEnabled($websiteId ?: null)) {
            return null;
        }
        $status = $this->storage->getStatus($customerId);
        return in_array($status, [Approval::STATUS_PENDING, Approval::STATUS_REJECTED], true) ? $status : null;
    }

    /**
     * @throws NotApprovedException
     */
    public function assertCanLogin(int $customerId, ?int $websiteId): void
    {
        $status = $this->getBlockingStatus($customerId, $websiteId);
        if ($status !== null) {
            throw new NotApprovedException(__($this->getMessage($status)));
        }
    }

    public function getMessage(string $status): string
    {
        $storeId = (int)$this->storeManager->getStore()->getId();
        if ($status === Approval::STATUS_REJECTED) {
            return $this->config->getRejectedMessage($storeId)
                ?: (string)__('Your account application was not approved. Please contact us if you think this is a mistake.');
        }
        return $this->config->getPendingMessage($storeId)
            ?: (string)__('Your account is waiting for approval. We will email you as soon as it has been reviewed.');
    }
}
