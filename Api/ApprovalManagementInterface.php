<?php
/**
 * CommerceGears Customer Approval
 *
 * @copyright Copyright (c) CommerceGears (https://commercegears.com/)
 */
declare(strict_types=1);

namespace CommerceGears\CustomerApproval\Api;

/**
 * Approve or reject customer accounts. Also exposed as admin REST endpoints (etc/webapi.xml).
 *
 * @api
 */
interface ApprovalManagementInterface
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    /**
     * Approval status of a customer: pending, approved or rejected.
     *
     * @param int $customerId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStatus(int $customerId): string;

    /**
     * Approve a customer account and (if enabled) email the customer.
     *
     * @param int $customerId
     * @param bool $notify
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function approve(int $customerId, bool $notify = true): bool;

    /**
     * Reject a customer account, sign the customer out everywhere and (if enabled) email the customer.
     *
     * @param int $customerId
     * @param string|null $reason
     * @param bool $notify
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function reject(int $customerId, ?string $reason = null, bool $notify = true): bool;

    /**
     * Put a customer account back to "pending".
     *
     * @param int $customerId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setPending(int $customerId): bool;
}
