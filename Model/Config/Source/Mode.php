<?php
/**
 * CommerceGears Customer Approval
 *
 * @copyright Copyright (c) CommerceGears (https://www.commercegearscommerce.co.uk/)
 */
declare(strict_types=1);

namespace CommerceGears\CustomerApproval\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use CommerceGears\CustomerApproval\Model\Config;

class Mode implements OptionSourceInterface
{
    /**
     * @return array<int, array{value: string, label: \Magento\Framework\Phrase}>
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => Config::MODE_ALL, 'label' => __('All new accounts')],
            ['value' => Config::MODE_GROUPS, 'label' => __('Only accounts in selected customer groups')],
        ];
    }
}
