<?php
/**
 * CommerceGears Customer Approval
 *
 * @copyright Copyright (c) CommerceGears (https://commercegears.com/)
 */
declare(strict_types=1);

namespace CommerceGears\CustomerApproval\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use CommerceGears\CustomerApproval\Api\ApprovalManagementInterface as Approval;

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
            $status = (string)($item['commercegears_approval_status_code'] ?? $item['commercegears_approval_status'] ?? '');
            $actions = [
                'edit' => [
                    'href' => $this->urlBuilder->getUrl('customer/index/edit', ['id' => $id]),
                    'label' => __('View Customer'),
                ],
            ];
            if ($status !== Approval::STATUS_APPROVED) {
                $actions['approve'] = [
                    'href' => $this->urlBuilder->getUrl('commercegears_customerapproval/customer/approve', ['id' => $id, 'back' => 'queue']),
                    'label' => __('Approve'),
                    // The grid only posts actions that have a confirm (or callback); without one it opens the
                    // link with GET, which the POST-only controller answers with a 404.
                    'post' => true,
                    'confirm' => [
                        'title' => __('Approve Account'),
                        'message' => __('Approve this account? The customer can then sign in.'),
                    ],
                ];
            }
            if ($status !== Approval::STATUS_REJECTED) {
                $actions['reject'] = [
                    'href' => $this->urlBuilder->getUrl('commercegears_customerapproval/customer/reject', ['id' => $id, 'back' => 'queue']),
                    'label' => __('Reject'),
                    'post' => true,
                    'confirm' => [
                        'title' => __('Reject Account'),
                        'message' => __('Reject this account? The customer cannot sign in. To add a reason, use the Reject button on the customer page.'),
                    ],
                ];
            }
            $dataSource['data']['items'][$i][$this->getData('name')] = $actions;
        }
        return $dataSource;
    }
}
