<?php
/**
 * CommerceGears Customer Approval
 *
 * @copyright Copyright (c) CommerceGears (https://www.commercegearscommerce.co.uk/)
 */
declare(strict_types=1);

namespace CommerceGears\CustomerApproval\Plugin\GraphQl;

use Magento\Integration\Model\Oauth\Token;
use CommerceGears\CustomerApproval\Model\LoginGuard;

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
     * @throws \CommerceGears\CustomerApproval\Exception\NotApprovedException
     */
    public function beforeCreateCustomerToken(Token $subject, $userId)
    {
        $this->loginGuard->assertCanLogin((int)$userId, null);
        return null;
    }
}
