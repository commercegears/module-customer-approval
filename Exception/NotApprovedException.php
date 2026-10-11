<?php
/**
 * CommerceGears Customer Approval
 *
 * @copyright Copyright (c) CommerceGears (https://commercegears.com/)
 */
declare(strict_types=1);

namespace CommerceGears\CustomerApproval\Exception;

use Magento\Framework\Exception\LocalizedException;

/**
 * Thrown when a pending or rejected customer tries to sign in. It is a LocalizedException (not an
 * AuthenticationException) so the storefront login form and the checkout login popup show its message.
 */
class NotApprovedException extends LocalizedException
{
}
