<?php
/**
 * CommerceGears Customer Approval
 *
 * @copyright Copyright (c) CommerceGears (https://www.commercegearscommerce.co.uk/)
 */
declare(strict_types=1);

namespace CommerceGears\CustomerApproval\Model;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Builds admin links for emails sent from storefront requests (the backend URL model needs an admin session).
 * When "Add Secret Key to URLs" is on, Magento opens the dashboard first and the link works after that.
 */
class AdminUrl
{
    public function __construct(
        private readonly FrontNameResolver $frontNameResolver,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    /**
     * @param array<string, string|int> $params
     */
    public function get(string $route, array $params = []): string
    {
        if ($this->scopeConfig->isSetFlag(FrontNameResolver::XML_PATH_USE_CUSTOM_ADMIN_URL)) {
            $base = (string)$this->scopeConfig->getValue(FrontNameResolver::XML_PATH_CUSTOM_ADMIN_URL);
        } else {
            $base = (string)$this->storeManager->getStore(Store::DEFAULT_STORE_ID)
                ->getBaseUrl(UrlInterface::URL_TYPE_LINK, true);
        }
        $path = rtrim($base, '/') . '/' . trim((string)$this->frontNameResolver->getFrontName(), '/') . '/' . trim($route, '/') . '/';
        foreach ($params as $key => $value) {
            $path .= rawurlencode((string)$key) . '/' . rawurlencode((string)$value) . '/';
        }
        return $path;
    }
}
