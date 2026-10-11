<?php
/**
 * CommerceGears Customer Approval
 *
 * @copyright Copyright (c) CommerceGears (https://www.commercegearscommerce.co.uk/)
 */
declare(strict_types=1);

namespace CommerceGears\CustomerApproval\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;
use CommerceGears\CustomerApproval\Model\Config;

/**
 * Text shown on the registration form when new accounts on this website need approval.
 */
class RegistrationNotice implements ArgumentInterface
{
    public function __construct(
        private readonly Config $config,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    public function getNotice(): string
    {
        $store = $this->storeManager->getStore();
        $storeId = (int)$store->getId();
        if (!$this->config->isEnabled((int)$store->getWebsiteId()) || !$this->config->isRegistrationNoticeEnabled($storeId)) {
            return '';
        }
        return trim($this->config->getRegistrationNotice($storeId));
    }
}
