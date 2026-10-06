<?php
/**
 * Softaware Customer Approval
 *
 * @copyright Copyright (c) Softaware Commerce (https://www.softawarecommerce.co.uk/)
 */
declare(strict_types=1);

namespace Softaware\CustomerApproval\Plugin;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Softaware\CustomerApproval\Api\ApprovalManagementInterface as Approval;
use Softaware\CustomerApproval\Model\LoginGuard;
use Softaware\CustomerApproval\Model\Notifier;
use Softaware\CustomerApproval\Model\PendingCounter;
use Softaware\CustomerApproval\Model\RegistrationState;
use Softaware\CustomerApproval\Model\ResourceModel\StatusStorage;
use Softaware\CustomerApproval\Model\RuleEvaluator;
use Softaware\CustomerApproval\Model\TrustedContext;

/**
 * - New accounts (storefront form, checkout, REST, GraphQL, social login...) get their status from the rules.
 *   Accounts created by the merchant (admin, admin API, CLI) are approved.
 * - authenticate() refuses pending and rejected customers. Every password login goes through it: login form
 *   (Luma and Hyvä), checkout login popup, REST and GraphQL customer token generation.
 *
 * createAccount() calls createAccountWithPasswordHash() internally (not intercepted), so both are covered.
 */
class AccountManagementPlugin
{
    public function __construct(
        private readonly RuleEvaluator $ruleEvaluator,
        private readonly TrustedContext $trustedContext,
        private readonly StatusStorage $storage,
        private readonly LoginGuard $loginGuard,
        private readonly RegistrationState $registrationState,
        private readonly Notifier $notifier,
        private readonly PendingCounter $pendingCounter
    ) {
    }

    /**
     * @param string|null $password
     * @param string $redirectUrl
     * @return null
     */
    public function beforeCreateAccount(
        AccountManagementInterface $subject,
        CustomerInterface $customer,
        $password = null,
        $redirectUrl = ''
    ) {
        $this->applyInitialStatus($customer);
        return null;
    }

    /**
     * @param string|null $hash
     * @param string $redirectUrl
     * @return null
     */
    public function beforeCreateAccountWithPasswordHash(
        AccountManagementInterface $subject,
        CustomerInterface $customer,
        $hash,
        $redirectUrl = ''
    ) {
        $this->applyInitialStatus($customer);
        return null;
    }

    public function afterCreateAccount(AccountManagementInterface $subject, CustomerInterface $result): CustomerInterface
    {
        $this->afterCreate($result);
        return $result;
    }

    public function afterCreateAccountWithPasswordHash(
        AccountManagementInterface $subject,
        CustomerInterface $result
    ): CustomerInterface {
        $this->afterCreate($result);
        return $result;
    }

    /**
     * @throws \Softaware\CustomerApproval\Exception\NotApprovedException
     */
    public function afterAuthenticate(AccountManagementInterface $subject, CustomerInterface $result): CustomerInterface
    {
        $this->loginGuard->assertCanLogin((int)$result->getId(), (int)$result->getWebsiteId());
        return $result;
    }

    private function applyInitialStatus(CustomerInterface $customer): void
    {
        if ($customer->getId()) {
            // Existing customer being added to another store: keep the current status.
            return;
        }
        // Always overwrite: a value sent by the customer (REST custom_attributes) must not be trusted.
        $status = $this->trustedContext->isTrusted() ? Approval::STATUS_APPROVED : $this->ruleEvaluator->evaluate($customer);
        $customer->setCustomAttribute(StatusStorage::STATUS_ATTRIBUTE, $status);
        $customer->setCustomAttribute(StatusStorage::REASON_ATTRIBUTE, null);
    }

    private function afterCreate(CustomerInterface $customer): void
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
