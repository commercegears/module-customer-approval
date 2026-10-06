<?php
/**
 * Softaware Customer Approval
 *
 * @copyright Copyright (c) Softaware Commerce (https://www.softawarecommerce.co.uk/)
 */
declare(strict_types=1);

namespace Softaware\CustomerApproval\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Softaware\CustomerApproval\Api\ApprovalManagementInterface as Approval;

class QueueActions extends Column
{
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        private readonly UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array<string, mixed> $dataSource
     * @return array<string, mixed>
     */
    public function prepareDataSource(array $dataSource): array
    {
        foreach ($dataSource['data']['items'] ?? [] as $i => $item) {
            $id = (int)($item['entity_id'] ?? 0);
            if (!$id) {
                continue;
            }
            $actions = [
                'edit' => [
                    'href' => $this->urlBuilder->getUrl('customer/index/edit', ['id' => $id]),
                    'label' => __('View customer'),
                ],
            ];
            if (($item['softaware_approval_status'] ?? '') !== Approval::STATUS_APPROVED) {
                $actions['approve'] = [
                    'href' => $this->urlBuilder->getUrl('softaware_customerapproval/customer/approve', ['id' => $id, 'back' => 'queue']),
                    'label' => __('Approve'),
                    'post' => true,
                ];
            }
            if (($item['softaware_approval_status'] ?? '') !== Approval::STATUS_REJECTED) {
                $actions['reject'] = [
                    'href' => $this->urlBuilder->getUrl('softaware_customerapproval/customer/reject', ['id' => $id, 'back' => 'queue']),
                    'label' => __('Reject'),
                    'post' => true,
                    'confirm' => [
                        'title' => __('Reject account'),
                        'message' => __('Reject this account? The customer cannot sign in.'),
                    ],
                ];
            }
            $dataSource['data']['items'][$i][$this->getData('name')] = $actions;
        }
        return $dataSource;
    }
}
