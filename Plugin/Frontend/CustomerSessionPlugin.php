<?php
/**
 * CommerceGears Customer Approval
 *
 * @copyright Copyright (c) CommerceGears (https://commercegears.com/)
 */
declare(strict_types=1);

namespace CommerceGears\CustomerApproval\Plugin\Frontend;

use Magento\Customer\Model\Session;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use CommerceGears\CustomerApproval\Model\LoginGuard;
use CommerceGears\CustomerApproval\Model\RegistrationState;

/**
 * Safety net for sign-ins that do not use a password (social login, "Login as Customer", email confirmation
 * link, auto sign-in after registration): pending and rejected customers are not signed in.
 */
class CustomerSessionPlugin
{
    public function __construct(
        private readonly LoginGuard $loginGuard,
        private readonly RegistrationState $registrationState,
        private readonly MessageManager $messageManager
    ) {
    }

    /**
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     */
    public function aroundSetCustomerDataAsLoggedIn(Session $subject, callable $proceed, $customer): Session
    {
        if ($this->isBlocked((int)$customer->getId(), (int)$customer->getWebsiteId())) {
            return $subject;
        }
        return $proceed($customer);
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     */
    public function aroundSetCustomerAsLoggedIn(Session $subject, callable $proceed, $customer): Session
    {
        if ($this->isBlocked((int)$customer->getId(), (int)$customer->getWebsiteId())) {
            return $subject;
        }
        return $proceed($customer);
    }

    /**
     * @param int $customerId
     */
    public function aroundLoginById(Session $subject, callable $proceed, $customerId): bool
    {
        if ($this->isBlocked((int)$customerId, null)) {
            return false;
        }
        return (bool)$proceed($customerId);
    }

    private function isBlocked(int $customerId, ?int $websiteId): bool
    {
        $status = $this->loginGuard->getBlockingStatus($customerId, $websiteId);
        if ($status === null) {
            return false;
        }
        $this->registrationState->setBlockedCustomerId($customerId);
        if ($this->registrationState->getPendingCustomerId() === $customerId) {
            // Registered in this request (registration form, social login, ...): explain instead of an error.
            if (!$this->registrationState->isMessageShown()) {
                $this->messageManager->addNoticeMessage($this->loginGuard->getRegistrationMessage());
                $this->registrationState->setMessageShown();
            }
            return true;
        }
        $this->messageManager->addErrorMessage($this->loginGuard->getMessage($status));
        return true;
    }
}
