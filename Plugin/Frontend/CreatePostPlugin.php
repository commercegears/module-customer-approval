<?php
/**
 * CommerceGears Customer Approval
 *
 * @copyright Copyright (c) CommerceGears (https://commercegears.com/)
 */
declare(strict_types=1);

namespace CommerceGears\CustomerApproval\Plugin\Frontend;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Controller\Account\CreatePost;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use CommerceGears\CustomerApproval\Model\Config;
use CommerceGears\CustomerApproval\Model\LoginGuard;
use CommerceGears\CustomerApproval\Model\RegistrationState;

/**
 * After a registration that needs approval: replace "Thank you for registering" (the customer is not signed in)
 * with the approval message and send the customer to the login page or the configured CMS page.
 * The registration form is the same controller in Luma and Hyvä.
 */
class CreatePostPlugin
{
    public function __construct(
        private readonly RegistrationState $registrationState,
        private readonly MessageManager $messageManager,
        private readonly AccountManagementInterface $accountManagement,
        private readonly RedirectFactory $redirectFactory,
        private readonly UrlInterface $url,
        private readonly Config $config,
        private readonly StoreManagerInterface $storeManager,
        private readonly LoginGuard $loginGuard
    ) {
    }

    public function afterExecute(CreatePost $subject, mixed $result): mixed
    {
        $customerId = $this->registrationState->getPendingCustomerId();
        if ($customerId === null) {
            return $result;
        }
        $storeId = (int)$this->storeManager->getStore()->getId();
        $needsConfirmation = $this->accountManagement->getConfirmationStatus($customerId)
            === AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED;
        if (!$needsConfirmation) {
            $this->removeSuccessMessages();
        }
        if (!$this->registrationState->isMessageShown()) {
            $this->messageManager->addNoticeMessage($this->loginGuard->getRegistrationMessage());
            $this->registrationState->setMessageShown();
        }
        if ($needsConfirmation) {
            return $result;
        }
        return $this->redirectFactory->create()->setUrl($this->getRedirectUrl($storeId));
    }

    private function removeSuccessMessages(): void
    {
        $collection = $this->messageManager->getMessages();
        $keep = array_filter(
            $collection->getItems(),
            static fn (MessageInterface $message) => $message->getType() !== MessageInterface::TYPE_SUCCESS
        );
        $collection->clear();
        foreach ($keep as $message) {
            $collection->addMessage($message);
        }
    }

    private function getRedirectUrl(int $storeId): string
    {
        $page = $this->config->getRedirectPage($storeId);
        if ($page !== '') {
            // CMS page source values are "identifier" or "identifier|id".
            $identifier = explode('|', $page)[0];
            return $this->url->getUrl(null, ['_direct' => $identifier]);
        }
        return $this->url->getUrl('customer/account/login', ['_secure' => true]);
    }
}
