<?php
/**
 * CommerceGears Customer Approval
 *
 * @copyright Copyright (c) CommerceGears (https://www.commercegearscommerce.co.uk/)
 */
declare(strict_types=1);

namespace CommerceGears\CustomerApproval\Model;

use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use CommerceGears\CustomerApproval\Api\ApprovalManagementInterface as Approval;
use CommerceGears\CustomerApproval\Exception\NotApprovedException;
use CommerceGears\CustomerApproval\Model\ResourceModel\StatusStorage;

/**
 * Decides whether a customer may sign in. Customers without a stored status (created before the module was
 * installed, imported, ...) count as approved.
 */
class LoginGuard
{
    public function __construct(
        private readonly Config $config,
        private readonly StatusStorage $storage,
        private readonly StoreManagerInterface $storeManager,
        private readonly CustomerRegistry $customerRegistry
    ) {
    }

    public function getBlockingStatus(int $customerId, ?int $websiteId): ?string
    {
        if (!$customerId) {
            return null;
        }
        if (!$websiteId) {
            // E.g. Session::loginById() ("Login as Customer"): the switch is per website, so use the customer's.
            $websiteId = $this->getCustomerWebsiteId($customerId);
        }
        if (!$this->config->isEnabled($websiteId ?: null)) {
            return null;
        }
        $status = $this->storage->getStatus($customerId);
        return in_array($status, [Approval::STATUS_PENDING, Approval::STATUS_REJECTED], true) ? $status : null;
    }

    private function getCustomerWebsiteId(int $customerId): ?int
    {
        try {
            return (int)$this->customerRegistry->retrieve($customerId)->getWebsiteId() ?: null;
        } catch (NoSuchEntityException) {
            return null;
        }
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

    /**
     * Shown right after a registration that needs approval (any registration path).
     */
    public function getRegistrationMessage(): string
    {
        return $this->config->getRegistrationMessage((int)$this->storeManager->getStore()->getId()) ?: (string)__(
            'Thank you for registering. Your account needs to be approved before you can sign in. '
            . 'We will email you as soon as it has been reviewed.'
        );
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
