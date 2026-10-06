<?php
/**
 * Softaware Customer Approval
 *
 * @copyright Copyright (c) Softaware Commerce (https://www.softawarecommerce.co.uk/)
 */
declare(strict_types=1);

namespace Softaware\CustomerApproval\Model;

/**
 * Request-scoped: remembers that the account created in this request is waiting for approval, so the
 * registration controller can show the right message instead of "Thank you for registering" + a login attempt.
 */
class RegistrationState
{
    private ?int $pendingCustomerId = null;

    /** @var array<int, true> */
    private array $handled = [];

    public function setPendingCustomerId(?int $customerId): void
    {
        $this->pendingCustomerId = $customerId;
    }

    public function getPendingCustomerId(): ?int
    {
        return $this->pendingCustomerId;
    }

    /**
     * Returns true the first time it is called for a customer in this request.
     */
    public function markHandled(int $customerId): bool
    {
        if (isset($this->handled[$customerId])) {
            return false;
        }
        $this->handled[$customerId] = true;
        return true;
    }
}
