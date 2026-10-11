<?php
/**
 * CommerceGears Customer Approval
 *
 * @copyright Copyright (c) CommerceGears (https://www.commercegearscommerce.co.uk/)
 */
declare(strict_types=1);

namespace CommerceGears\CustomerApproval\Block\Adminhtml\Edit;

use CommerceGears\CustomerApproval\Api\ApprovalManagementInterface as Approval;

class ApproveButton extends ApprovalButtonData
{
    /**
     * @return array<string, mixed>
     */
    public function getButtonData(): array
    {
        $status = $this->getCurrentStatus();
        if ($status === null || $status === Approval::STATUS_APPROVED) {
            return [];
        }
        return $this->buildButton((string)__('Approve'), 'commercegears-approve', 'approve', [
            'mode' => 'approve',
            'title' => (string)__('Approve Account'),
            'message' => (string)__('Approve this account? The customer can sign in straight away and gets the "account approved" email if it is enabled.'),
            'acceptLabel' => (string)__('Approve'),
            'cancelLabel' => (string)__('Cancel'),
        ], 35);
    }
}
