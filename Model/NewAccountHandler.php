<?php
/**
 * CommerceGears Customer Approval
 *
 * @copyright Copyright (c) CommerceGears (https://www.commercegearscommerce.co.uk/)
 */
declare(strict_types=1);

namespace CommerceGears\CustomerApproval\Model;

use Magento\Customer\Api\Data\CustomerInterface;
use CommerceGears\CustomerApproval\Api\ApprovalManagementInterface as Approval;
use CommerceGears\CustomerApproval\Model\ResourceModel\StatusStorage;

/**
 * Initial approval status of a new account and the follow-up (shop-team email, pending counter).
 *
 * Used for accounts created through AccountManagement (registration form, checkout, REST, GraphQL) and for
 * accounts created directly through the customer repository (social login and other modules), so no way of
 * creating an account skips the approval rules.
 */
class NewAccountHandler
{
    public function __construct(
        private readonly RuleEvaluator $ruleEvaluator,
        private readonly TrustedContext $trustedContext,
        private readonly StatusStorage $storage,
        private readonly RegistrationState $registrationState,
        private readonly Notifier $notifier,
        private readonly PendingCounter $pendingCounter
    ) {
    }

    /**
     * Sets the status of a customer that is about to be created. Always overwrites: a value sent by the
     * customer (REST custom_attributes, a form field) must not be trusted.
     */
    public function applyInitialStatus(CustomerInterface $customer): void
    {
        if ($customer->getId()) {
            // Existing customer being added to another store: keep the current status.
            return;
        }
        $status = $this->trustedContext->isTrusted() ? Approval::STATUS_APPROVED : $this->ruleEvaluator->evaluate($customer);
        $customer->setCustomAttribute(StatusStorage::STATUS_ATTRIBUTE, $status);
        $customer->setCustomAttribute(StatusStorage::REASON_ATTRIBUTE, null);
    }

    /**
     * Runs once per new customer and request: remembers a pending registration and informs the shop team.
     */
    public function afterCreate(CustomerInterface $customer): void
    {
        $customerId = (int)$customer->getId();
        if (!$customerId || !$this->registrationState->markHandled($customerId)) {
            return;
        }
        $this->storage->clearCache($customerId);
        if ($this->storage->getStatus($customerId) !== Approval::STATUS_PENDING) {
            return;
        }
        $this->registrationState->setPendingCustomerId($customerId);
        $this->pendingCounter->invalidate();
        $this->notifier->notifyAdmin($customer);
    }
}
