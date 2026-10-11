<?php
/**
 * CommerceGears Customer Approval
 *
 * @copyright Copyright (c) CommerceGears (https://www.commercegearscommerce.co.uk/)
 */
declare(strict_types=1);

namespace CommerceGears\CustomerApproval\Model;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\App\Area;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface as InlineTranslation;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Emails: new registration to the shop team, approved/rejected to the customer.
 * Failures are logged and never stop the registration or the approval.
 */
class Notifier
{
    public function __construct(
        private readonly Config $config,
        private readonly TransportBuilder $transportBuilder,
        private readonly InlineTranslation $inlineTranslation,
        private readonly StoreManagerInterface $storeManager,
        private readonly GroupRepositoryInterface $groupRepository,
        private readonly CustomerRegistry $customerRegistry,
        private readonly AdminUrl $adminUrl,
        private readonly LoggerInterface $logger
    ) {
    }

    public function notifyAdmin(CustomerInterface $customer): void
    {
        $websiteId = (int)$customer->getWebsiteId();
        $storeId = (int)$customer->getStoreId();
        $recipients = $this->config->getAdminRecipients($websiteId);
        if (!$this->config->isAdminNotificationEnabled($websiteId) || $recipients === []) {
            return;
        }
        $this->send($this->config->getAdminTemplate($storeId), $storeId, $recipients, '', [
            'customer_name' => $this->getName($customer),
            'customer_email' => (string)$customer->getEmail(),
            'group_name' => $this->getGroupName((int)$customer->getGroupId()),
            'website_name' => $this->getWebsiteName($websiteId),
            'created_at' => (string)$customer->getCreatedAt(),
            'customer_url' => $this->adminUrl->get('customer/index/edit', ['id' => (int)$customer->getId()]),
            'queue_url' => $this->adminUrl->get('commercegears_customerapproval/queue/index'),
        ]);
    }

    public function notifyApproved(CustomerInterface $customer): void
    {
        $storeId = (int)$customer->getStoreId();
        if (!$this->config->isApprovedEmailEnabled($storeId)) {
            return;
        }
        $this->send(
            $this->config->getApprovedTemplate($storeId),
            $storeId,
            [(string)$customer->getEmail()],
            $this->getName($customer),
            $this->getCustomerVars($customer)
        );
    }

    public function notifyRejected(CustomerInterface $customer, ?string $reason): void
    {
        $storeId = (int)$customer->getStoreId();
        if (!$this->config->isRejectedEmailEnabled($storeId)) {
            return;
        }
        $reason = $this->config->isReasonIncluded($storeId) ? trim((string)$reason) : '';
        $this->send(
            $this->config->getRejectedTemplate($storeId),
            $storeId,
            [(string)$customer->getEmail()],
            $this->getName($customer),
            $this->getCustomerVars($customer) + ['reason' => $reason, 'has_reason' => $reason !== '']
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function getCustomerVars(CustomerInterface $customer): array
    {
        $vars = [
            'customer_name' => $this->getName($customer),
            'customer_email' => (string)$customer->getEmail(),
        ];
        try {
            // Lets the templates use {{var customer.name}} like Magento's own customer emails.
            $vars['customer'] = $this->customerRegistry->retrieve((int)$customer->getId());
        } catch (\Throwable) {
            $vars['customer'] = null;
        }
        return $vars;
    }

    /**
     * @param string[] $recipients
     * @param array<string, mixed> $vars
     */
    private function send(string $templateId, int $storeId, array $recipients, string $name, array $vars): void
    {
        if ($templateId === '' || $recipients === []) {
            return;
        }
        $this->inlineTranslation->suspend();
        try {
            $store = $this->storeManager->getStore($storeId ?: null);
            $this->transportBuilder
                ->setTemplateIdentifier($templateId)
                ->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => (int)$store->getId()])
                ->setTemplateVars($vars + ['store' => $store])
                ->setFromByScope($this->config->getSender((int)$store->getId()), (int)$store->getId());
            foreach ($recipients as $email) {
                $this->transportBuilder->addTo($email, $name);
            }
            $this->transportBuilder->getTransport()->sendMessage();
        } catch (\Throwable $e) {
            $this->logger->error('CommerceGears_CustomerApproval: could not send email ' . $templateId . ': ' . $e->getMessage());
        } finally {
            $this->inlineTranslation->resume();
        }
    }

    private function getName(CustomerInterface $customer): string
    {
        return trim(implode(' ', array_filter([(string)$customer->getFirstname(), (string)$customer->getLastname()])));
    }

    private function getGroupName(int $groupId): string
    {
        try {
            return (string)$this->groupRepository->getById($groupId)->getCode();
        } catch (\Throwable) {
            return (string)$groupId;
        }
    }

    private function getWebsiteName(int $websiteId): string
    {
        try {
            return (string)$this->storeManager->getWebsite($websiteId)->getName();
        } catch (\Throwable) {
            return (string)$websiteId;
        }
    }
}
