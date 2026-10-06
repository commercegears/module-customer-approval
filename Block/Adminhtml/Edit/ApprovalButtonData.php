<?php
/**
 * Softaware Customer Approval
 *
 * @copyright Copyright (c) Softaware Commerce (https://www.softawarecommerce.co.uk/)
 */
declare(strict_types=1);

namespace Softaware\CustomerApproval\Block\Adminhtml\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Softaware\CustomerApproval\Api\ApprovalManagementInterface as Approval;
use Softaware\CustomerApproval\Model\ResourceModel\StatusStorage;

/**
 * Shared logic of the "Approve" and "Reject" buttons on the customer edit page.
 */
abstract class ApprovalButtonData extends \Magento\Customer\Block\Adminhtml\Edit\GenericButton implements ButtonProviderInterface
{
    public function __construct(
        Context $context,
        Registry $registry,
        protected readonly StatusStorage $storage,
        protected readonly AuthorizationInterface $authorization
    ) {
        parent::__construct($context, $registry);
    }

    protected function getCurrentStatus(): ?string
    {
        $customerId = (int)$this->getCustomerId();
        if (!$customerId || !$this->authorization->isAllowed('Softaware_CustomerApproval::manage')) {
            return null;
        }
        return $this->storage->getStatus($customerId) ?? Approval::STATUS_APPROVED;
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildButton(string $label, string $class, string $action, array $widgetOptions, int $sortOrder): array
    {
        return [
            'label' => $label,
            'class' => $class,
            'sort_order' => $sortOrder,
            'on_click' => '',
            'data_attribute' => [
                'mage-init' => [
                    'Softaware_CustomerApproval/js/approval-action' => $widgetOptions + [
                        'url' => $this->getUrl('softaware_customerapproval/customer/' . $action, ['id' => $this->getCustomerId()]),
                    ],
                ],
            ],
        ];
    }
}
