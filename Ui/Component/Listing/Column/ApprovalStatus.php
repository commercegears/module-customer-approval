<?php
/**
 * CommerceGears Customer Approval
 *
 * @copyright Copyright (c) CommerceGears (https://commercegears.com/)
 */
declare(strict_types=1);

namespace CommerceGears\CustomerApproval\Ui\Component\Listing\Column;

use Magento\Framework\Escaper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use CommerceGears\CustomerApproval\Api\ApprovalManagementInterface as Approval;
use CommerceGears\CustomerApproval\Model\Attribute\Source\Status;

/**
 * Shows the approval status as a Magento grid severity badge.
 */
class ApprovalStatus extends Column
{
    private const SEVERITY = [
        Approval::STATUS_PENDING => 'grid-severity-minor',
        Approval::STATUS_APPROVED => 'grid-severity-notice',
        Approval::STATUS_REJECTED => 'grid-severity-critical',
    ];

    /** Short badge texts (the filter keeps the full option labels) */
    private const BADGE_LABELS = [
        Approval::STATUS_PENDING => 'Pending',
        Approval::STATUS_APPROVED => 'Approved',
        Approval::STATUS_REJECTED => 'Rejected',
    ];

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        private readonly Status $status,
        private readonly Escaper $escaper,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }
        $name = $this->getData('name');
        foreach ($dataSource['data']['items'] as &$item) {
            $value = $item[$name] ?? '';
            // the customer grid data provider turns option values into arrays
            $value = is_array($value) ? (string)(reset($value) ?: '') : (string)$value;
            if ($value === '') {
                continue;
            }
            // keep the raw code for the actions column, which is prepared after this one
            $item[$name . '_code'] = $value;
            $label = isset(self::BADGE_LABELS[$value])
                ? (string)__(self::BADGE_LABELS[$value])
                : (string)($this->status->getOptionText($value) ?: $value);
            $item[$name] = sprintf(
                '<span class="%s"><span>%s</span></span>',
                self::SEVERITY[$value] ?? 'grid-severity-notice',
                $this->escaper->escapeHtml($label)
            );
        }
        unset($item);
        return $dataSource;
    }
}
