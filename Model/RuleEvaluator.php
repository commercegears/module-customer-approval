<?php
/**
 * Softaware Customer Approval
 *
 * @copyright Copyright (c) Softaware Commerce (https://www.softawarecommerce.co.uk/)
 */
declare(strict_types=1);

namespace Softaware\CustomerApproval\Model;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Store\Model\StoreManagerInterface;
use Softaware\CustomerApproval\Api\ApprovalManagementInterface as Approval;

/**
 * Decides the initial status of a new account from the website's rules.
 */
class RuleEvaluator
{
    public function __construct(
        private readonly Config $config,
        private readonly StoreManagerInterface $storeManager,
        private readonly GroupManagementInterface $groupManagement
    ) {
    }

    public function evaluate(CustomerInterface $customer): string
    {
        $storeId = $customer->getStoreId() ? (int)$customer->getStoreId() : null;
        $websiteId = $customer->getWebsiteId()
            ? (int)$customer->getWebsiteId()
            : (int)$this->storeManager->getStore($storeId)->getWebsiteId();
        if (!$this->config->isEnabled($websiteId)) {
            return Approval::STATUS_APPROVED;
        }
        if ($this->isDomainAutoApproved((string)$customer->getEmail(), $websiteId)) {
            return Approval::STATUS_APPROVED;
        }
        $groupId = $customer->getGroupId() !== null && $customer->getGroupId() !== ''
            ? (int)$customer->getGroupId()
            : (int)$this->groupManagement->getDefaultGroup($storeId)->getId();
        if ($this->config->getMode($websiteId) === Config::MODE_GROUPS) {
            return in_array($groupId, $this->config->getRequiredGroups($websiteId), true)
                ? Approval::STATUS_PENDING
                : Approval::STATUS_APPROVED;
        }
        return in_array($groupId, $this->config->getAutoApproveGroups($websiteId), true)
            ? Approval::STATUS_APPROVED
            : Approval::STATUS_PENDING;
    }

    public function isDomainAutoApproved(string $email, int $websiteId): bool
    {
        $at = strrpos($email, '@');
        if ($at === false) {
            return false;
        }
        $domain = strtolower(substr($email, $at + 1));
        foreach ($this->config->getAutoApproveDomains($websiteId) as $allowed) {
            if ($domain === $allowed) {
                return true;
            }
            if (str_starts_with($allowed, '*.') && str_ends_with($domain, substr($allowed, 1))) {
                return true;
            }
        }
        return false;
    }
}
