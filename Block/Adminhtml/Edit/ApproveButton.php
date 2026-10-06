<?php
/**
 * Softaware Customer Approval
 *
 * @copyright Copyright (c) Softaware Commerce (https://www.softawarecommerce.co.uk/)
 */
declare(strict_types=1);

namespace Softaware\CustomerApproval\Block\Adminhtml\Edit;

use Softaware\CustomerApproval\Api\ApprovalManagementInterface as Approval;

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
        return $this->buildButton((string)__('Approve'), 'softaware-approve', 'approve', [
            'mode' => 'approve',
            'title' => (string)__('Approve customer'),
            'message' => (string)__('Approve this account? The customer can sign in straight away and gets the "account approved" email if it is enabled.'),
        ], 35);
    }
}
