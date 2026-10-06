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
use Softaware\CustomerApproval\Model\LoginGuard;
use Softaware\CustomerApproval\Model\NewAccountHandler;

/**
 * - New accounts (storefront form, checkout, REST, GraphQL...) get their status from the rules.
 *   Accounts created by the merchant (admin, admin API, CLI) are approved. Accounts saved directly through the
 *   customer repository (social login, other modules) are handled by CustomerRepositoryPlugin.
 * - authenticate() refuses pending and rejected customers. Every password login goes through it: login form
 *   (Luma and Hyvä), checkout login popup, REST and GraphQL customer token generation, RSS feed login.
 *
 * createAccount() calls createAccountWithPasswordHash() internally (not intercepted), so both are covered.
 */
class AccountManagementPlugin
{
    public function __construct(
        private readonly NewAccountHandler $newAccountHandler,
        private readonly LoginGuard $loginGuard
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
        $this->newAccountHandler->applyInitialStatus($customer);
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
        $this->newAccountHandler->applyInitialStatus($customer);
        return null;
    }

    public function afterCreateAccount(AccountManagementInterface $subject, CustomerInterface $result): CustomerInterface
    {
        $this->newAccountHandler->afterCreate($result);
        return $result;
    }

    public function afterCreateAccountWithPasswordHash(
        AccountManagementInterface $subject,
        CustomerInterface $result
    ): CustomerInterface {
        $this->newAccountHandler->afterCreate($result);
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
}
