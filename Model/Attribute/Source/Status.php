<?php
/**
 * CommerceGears Customer Approval
 *
 * @copyright Copyright (c) CommerceGears (https://commercegears.com/)
 */
declare(strict_types=1);

namespace CommerceGears\CustomerApproval\Model\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use CommerceGears\CustomerApproval\Api\ApprovalManagementInterface as Approval;

/**
 * Options of the customer attribute "commercegears_approval_status" (also used by the grids' filters).
 */
class Status extends AbstractSource
{
    /**
     * @return array<int, array{value: string, label: \Magento\Framework\Phrase}>
     */
    public function getAllOptions(): array
    {
        return [
            ['value' => Approval::STATUS_PENDING, 'label' => __('Pending approval')],
            ['value' => Approval::STATUS_APPROVED, 'label' => __('Approved')],
            ['value' => Approval::STATUS_REJECTED, 'label' => __('Rejected')],
        ];
    }
}
