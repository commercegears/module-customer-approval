<?php
/**
 * Softaware Customer Approval
 *
 * @copyright Copyright (c) Softaware Commerce (https://www.softawarecommerce.co.uk/)
 */
declare(strict_types=1);

namespace Softaware\CustomerApproval\Model;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;

/**
 * True when the current request is made by the merchant (admin panel, admin/integration API token, CLI),
 * as opposed to a customer or guest. Accounts created by the merchant are approved straight away and only
 * the merchant may change the approval attributes.
 */
class TrustedContext
{
    public function __construct(
        private readonly State $appState,
        private readonly UserContextInterface $userContext
    ) {
    }

    public function isTrusted(): bool
    {
        try {
            $area = $this->appState->getAreaCode();
        } catch (\Throwable) {
            return PHP_SAPI === 'cli';
        }
        if ($area === Area::AREA_ADMINHTML || $area === Area::AREA_CRONTAB) {
            return true;
        }
        if (PHP_SAPI === 'cli' && $area !== Area::AREA_FRONTEND) {
            return true;
        }
        if (in_array($area, [Area::AREA_WEBAPI_REST, Area::AREA_WEBAPI_SOAP, Area::AREA_GRAPHQL], true)) {
            return in_array(
                (int)$this->userContext->getUserType(),
                [UserContextInterface::USER_TYPE_ADMIN, UserContextInterface::USER_TYPE_INTEGRATION],
                true
            );
        }
        return false;
    }
}
