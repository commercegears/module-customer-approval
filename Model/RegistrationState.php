<?php
/**
 * CommerceGears Customer Approval
 *
 * @copyright Copyright (c) CommerceGears (https://www.commercegearscommerce.co.uk/)
 */
declare(strict_types=1);

namespace CommerceGears\CustomerApproval\Model;

/**
 * Request-scoped: remembers that the account created in this request is waiting for approval, so the
 * registration controller can show the right message instead of "Thank you for registering" + a login attempt.
 */
class RegistrationState
{
    private ?int $pendingCustomerId = null;

    /** @var array<int, true> */
    private array $handled = [];

    private bool $messageShown = false;

    private ?int $blockedCustomerId = null;

    public function setPendingCustomerId(?int $customerId): void
    {
        $this->pendingCustomerId = $customerId;
    }

    public function getPendingCustomerId(): ?int
    {
        return $this->pendingCustomerId;
    }

    /**
     * A password-less sign-in of this customer was refused in this request.
     */
    public function setBlockedCustomerId(?int $customerId): void
    {
        $this->blockedCustomerId = $customerId;
    }

    public function getBlockedCustomerId(): ?int
    {
        return $this->blockedCustomerId;
    }

    /**
     * The "waiting for approval" registration message has been added in this request.
     */
    public function setMessageShown(bool $shown = true): void
    {
        $this->messageShown = $shown;
    }

    public function isMessageShown(): bool
    {
        return $this->messageShown;
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
