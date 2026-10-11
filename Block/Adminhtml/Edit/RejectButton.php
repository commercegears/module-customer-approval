<?php
/**
 * CommerceGears Customer Approval
 *
 * @copyright Copyright (c) CommerceGears (https://commercegears.com/)
 */
declare(strict_types=1);

namespace CommerceGears\CustomerApproval\Block\Adminhtml\Edit;

use CommerceGears\CustomerApproval\Api\ApprovalManagementInterface as Approval;

class RejectButton extends ApprovalButtonData
{
    /**
     * @return array<string, mixed>
     */
    public function getButtonData(): array
    {
        $status = $this->getCurrentStatus();
        if ($status === null || $status === Approval::STATUS_REJECTED) {
            return [];
        }
        return $this->buildButton((string)__('Reject'), 'commercegears-reject', 'reject', [
            'mode' => 'reject',
            'title' => (string)__('Reject Account'),
            'message' => (string)__('Reject this account? The customer is signed out and cannot sign in.'),
            'fieldLabel' => (string)__('Reason (optional; stored with the account and included in the email if enabled)'),
            'acceptLabel' => (string)__('Reject'),
            'cancelLabel' => (string)__('Cancel'),
        ], 36);
    }
}
