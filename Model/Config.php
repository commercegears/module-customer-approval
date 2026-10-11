<?php
/**
 * CommerceGears Customer Approval
 *
 * @copyright Copyright (c) CommerceGears (https://www.commercegearscommerce.co.uk/)
 */
declare(strict_types=1);

namespace CommerceGears\CustomerApproval\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Settings under Stores > Configuration > CommerceGears > Customer Approval.
 */
class Config
{
    public const MODE_ALL = 'all';
    public const MODE_GROUPS = 'groups';

    private const PREFIX = 'commercegears_customerapproval/';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    public function isEnabled(?int $websiteId = null): bool
    {
        return $this->flag('general/enabled', ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }

    public function isEnabledAnywhere(): bool
    {
        foreach ($this->storeManager->getWebsites() as $website) {
            if ($this->isEnabled((int)$website->getId())) {
                return true;
            }
        }
        return false;
    }

    public function getMode(?int $websiteId): string
    {
        $mode = (string)$this->value('general/mode', ScopeInterface::SCOPE_WEBSITE, $websiteId);
        return $mode === self::MODE_GROUPS ? self::MODE_GROUPS : self::MODE_ALL;
    }

    /**
     * @return int[]
     */
    public function getRequiredGroups(?int $websiteId): array
    {
        return $this->intList('general/groups', $websiteId);
    }

    /**
     * @return int[]
     */
    public function getAutoApproveGroups(?int $websiteId): array
    {
        return $this->intList('general/auto_approve_groups', $websiteId);
    }

    /**
     * @return string[] lower-case domains, "*.example.com" allows sub-domains
     */
    public function getAutoApproveDomains(?int $websiteId): array
    {
        $value = (string)$this->value('general/auto_approve_domains', ScopeInterface::SCOPE_WEBSITE, $websiteId);
        $domains = [];
        foreach (preg_split('/[\s,;]+/', strtolower($value)) ?: [] as $domain) {
            $domain = ltrim(trim($domain), '@');
            if ($domain !== '') {
                $domains[] = $domain;
            }
        }
        return $domains;
    }

    public function isRegistrationNoticeEnabled(?int $storeId = null): bool
    {
        return $this->flag('frontend/show_registration_notice', ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getRegistrationNotice(?int $storeId = null): string
    {
        return (string)$this->value('frontend/registration_notice', ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getRegistrationMessage(?int $storeId = null): string
    {
        return (string)$this->value('frontend/registration_message', ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getPendingMessage(?int $storeId = null): string
    {
        return (string)$this->value('frontend/pending_message', ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getRejectedMessage(?int $storeId = null): string
    {
        return (string)$this->value('frontend/rejected_message', ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getRedirectPage(?int $storeId = null): string
    {
        return (string)$this->value('frontend/redirect_page', ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function isAdminNotificationEnabled(?int $websiteId): bool
    {
        return $this->flag('admin_notification/enabled', ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }

    /**
     * @return string[]
     */
    public function getAdminRecipients(?int $websiteId): array
    {
        $value = (string)$this->value('admin_notification/recipients', ScopeInterface::SCOPE_WEBSITE, $websiteId);
        $emails = preg_split('/[\s,;]+/', $value) ?: [];
        return array_values(array_filter($emails, static fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL)));
    }

    public function getAdminTemplate(?int $storeId): string
    {
        return (string)$this->value('admin_notification/template', ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getSender(?int $storeId): string
    {
        return (string)($this->value('customer_notification/sender', ScopeInterface::SCOPE_STORE, $storeId) ?: 'general');
    }

    public function isApprovedEmailEnabled(?int $storeId): bool
    {
        return $this->flag('customer_notification/approved_enabled', ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getApprovedTemplate(?int $storeId): string
    {
        return (string)$this->value('customer_notification/approved_template', ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function isRejectedEmailEnabled(?int $storeId): bool
    {
        return $this->flag('customer_notification/rejected_enabled', ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getRejectedTemplate(?int $storeId): string
    {
        return (string)$this->value('customer_notification/rejected_template', ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function isReasonIncluded(?int $storeId): bool
    {
        return $this->flag('customer_notification/include_reason', ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @return int[]
     */
    private function intList(string $path, ?int $websiteId): array
    {
        $value = (string)$this->value($path, ScopeInterface::SCOPE_WEBSITE, $websiteId);
        return array_map('intval', array_filter(explode(',', $value), static fn ($v) => $v !== ''));
    }

    private function value(string $path, string $scope, ?int $scopeId): mixed
    {
        return $this->scopeConfig->getValue(self::PREFIX . $path, $scope, $scopeId);
    }

    private function flag(string $path, string $scope, ?int $scopeId): bool
    {
        return $this->scopeConfig->isSetFlag(self::PREFIX . $path, $scope, $scopeId);
    }
}
