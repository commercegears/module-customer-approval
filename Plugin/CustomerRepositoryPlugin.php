<?php
/**
 * CommerceGears Customer Approval
 *
 * @copyright Copyright (c) CommerceGears (https://www.commercegearscommerce.co.uk/)
 */
declare(strict_types=1);

namespace CommerceGears\CustomerApproval\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use CommerceGears\CustomerApproval\Api\ApprovalManagementInterface as Approval;
use CommerceGears\CustomerApproval\Model\NewAccountHandler;
use CommerceGears\CustomerApproval\Model\ResourceModel\StatusStorage;
use CommerceGears\CustomerApproval\Model\TrustedContext;

/**
 * - New customers saved directly through the repository (social login, other modules) get their status from the
 *   approval rules, like registrations through AccountManagement. Without this they would have no status, which
 *   counts as approved.
 * - Customers cannot change their own approval status (for example via PUT /V1/customers/me custom_attributes):
 *   outside the admin the stored values are always written back.
 */
class CustomerRepositoryPlugin
{
    /** @var array<int, true> spl_object_id of new customers being saved in this request */
    private array $creating = [];

    public function __construct(
        private readonly StatusStorage $storage,
        private readonly TrustedContext $trustedContext,
        private readonly NewAccountHandler $newAccountHandler
    ) {
    }

    /**
     * @param string|null $passwordHash
     * @return null
     */
    public function beforeSave(CustomerRepositoryInterface $subject, CustomerInterface $customer, $passwordHash = null)
    {
        $customerId = (int)$customer->getId();
        if (!$customerId) {
            $this->newAccountHandler->applyInitialStatus($customer);
            $this->creating[spl_object_id($customer)] = true;
            return null;
        }
        if ($this->trustedContext->isTrusted()) {
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

    /**
     * @param string|null $passwordHash
     */
    public function afterSave(
        CustomerRepositoryInterface $subject,
        CustomerInterface $result,
        CustomerInterface $customer,
        $passwordHash = null
    ): CustomerInterface {
        $key = spl_object_id($customer);
        if (isset($this->creating[$key])) {
            unset($this->creating[$key]);
            $this->newAccountHandler->afterCreate($result);
        }
        return $result;
    }
}
