<?php
/**
 * CommerceGears Customer Approval
 *
 * @copyright Copyright (c) CommerceGears (https://commercegears.com/)
 */
declare(strict_types=1);

namespace CommerceGears\CustomerApproval\Plugin\Frontend;

use Magento\Customer\Controller\Account\Confirm;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\UrlInterface;
use CommerceGears\CustomerApproval\Model\RegistrationState;

/**
 * Email confirmation link of an account that still needs approval: the account is confirmed, but the customer
 * is not signed in. Drop Magento's "Thank you for registering" and go to the sign-in page, where the
 * "waiting for approval" message is shown.
 */
class ConfirmPlugin
{
    public function __construct(
        private readonly RegistrationState $registrationState,
        private readonly MessageManager $messageManager,
        private readonly RedirectFactory $redirectFactory,
        private readonly UrlInterface $url
    ) {
    }

    public function afterExecute(Confirm $subject, mixed $result): mixed
    {
        if ($this->registrationState->getBlockedCustomerId() === null) {
            return $result;
        }
        $collection = $this->messageManager->getMessages();
        $keep = array_filter(
            $collection->getItems(),
            static fn (MessageInterface $message) => $message->getType() !== MessageInterface::TYPE_SUCCESS
        );
        $collection->clear();
        foreach ($keep as $message) {
            $collection->addMessage($message);
        }
        return $this->redirectFactory->create()->setUrl($this->url->getUrl('customer/account/login', ['_secure' => true]));
    }
}
