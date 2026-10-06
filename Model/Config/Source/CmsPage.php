<?php
/**
 * Softaware Customer Approval
 *
 * @copyright Copyright (c) Softaware Commerce (https://www.softawarecommerce.co.uk/)
 */
declare(strict_types=1);

namespace Softaware\CustomerApproval\Model\Config\Source;

use Magento\Cms\Model\Config\Source\Page;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * CMS pages plus "Login page" (empty value).
 */
class CmsPage implements OptionSourceInterface
{
    public function __construct(
        private readonly Page $pageSource
    ) {
    }

    /**
     * @return array<int, array{value: string, label: mixed}>
     */
    public function toOptionArray(): array
    {
        return array_merge(
            [['value' => '', 'label' => __('Login page')]],
            $this->pageSource->toOptionArray()
        );
    }
}
