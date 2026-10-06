<?php
/**
 * Softaware Customer Approval
 *
 * @copyright Copyright (c) Softaware Commerce (https://www.softawarecommerce.co.uk/)
 */
declare(strict_types=1);

namespace Softaware\CustomerApproval\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Softaware\CustomerApproval\Api\ApprovalManagementInterface as Approval;
use Softaware\CustomerApproval\Model\ResourceModel\StatusStorage;
use Softaware\CustomerApproval\Model\TrustedContext;

/**
 * Customers cannot change their own approval status (for example via PUT /V1/customers/me custom_attributes):
 * outside the admin the stored values are always written back.
 */
class CustomerRepositoryPlugin
{
    public function __construct(
        private readonly StatusStorage $storage,
        private readonly TrustedContext $trustedContext
    ) {
    }

    /**
     * @param string|null $passwordHash
     * @return null
     */
    public function beforeSave(CustomerRepositoryInterface $subject, CustomerInterface $customer, $passwordHash = null)
    {
        $customerId = (int)$customer->getId();
        if (!$customerId || $this->trustedContext->isTrusted()) {
            return null;
        }
        if ($customer->getCustomAttribute(StatusStorage::STATUS_ATTRIBUTE) !== null) {
            $customer->setCustomAttribute(
                StatusStorage::STATUS_ATTRIBUTE,
                $this->storage->getStatus($customerId) ?? Approval::STATUS_APPROVED
            );
        }
        if ($customer->getCustomAttribute(StatusStorage::REASON_ATTRIBUTE) !== null) {
            $customer->setCustomAttribute(StatusStorage::REASON_ATTRIBUTE, $this->storage->getReason($customerId));
        }
        return null;
    }
}
