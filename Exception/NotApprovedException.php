<?php
/**
 * Softaware Customer Approval
 *
 * @copyright Copyright (c) Softaware Commerce (https://www.softawarecommerce.co.uk/)
 */
declare(strict_types=1);

namespace Softaware\CustomerApproval\Exception;

use Magento\Framework\Exception\LocalizedException;

/**
 * Thrown when a pending or rejected customer tries to sign in. It is a LocalizedException (not an
 * AuthenticationException) so the storefront login form and the checkout login popup show its message.
 */
class NotApprovedException extends LocalizedException
{
}
