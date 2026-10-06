<?php
/**
 * Softaware Customer Approval
 *
 * @copyright Copyright (c) Softaware Commerce (https://www.softawarecommerce.co.uk/)
 */
declare(strict_types=1);

namespace Softaware\CustomerApproval\Plugin\GraphQl;

use Magento\Integration\Model\Oauth\Token;
use Softaware\CustomerApproval\Model\LoginGuard;

/**
 * GraphQL "generateCustomerTokenAsAdmin" (Login as Customer for headless storefronts) creates a customer token
 * directly, without a password check. Pending and rejected customers get no token, as on the storefront.
 */
class CustomerTokenPlugin
{
    public function __construct(
        private readonly LoginGuard $loginGuard
    ) {
    }

    /**
     * @param int $userId
     * @return null
     * @throws \Softaware\CustomerApproval\Exception\NotApprovedException
     */
    public function beforeCreateCustomerToken(Token $subject, $userId)
    {
        $this->loginGuard->assertCanLogin((int)$userId, null);
        return null;
    }
}
