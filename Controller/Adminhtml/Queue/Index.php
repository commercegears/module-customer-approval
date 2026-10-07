<?php
/**
 * Softaware Customer Approval
 *
 * @copyright Copyright (c) Softaware Commerce (https://www.softawarecommerce.co.uk/)
 */
declare(strict_types=1);

namespace Softaware\CustomerApproval\Controller\Adminhtml\Queue;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\Page;
use Softaware\CustomerApproval\Api\ApprovalManagementInterface as Approval;
use Softaware\CustomerApproval\Model\ResourceModel\StatusStorage;

/**
 * Approval queue: pending and rejected accounts. Shows an explanation instead of an empty table.
 */
class Index extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Softaware_CustomerApproval::manage';

    public function __construct(
        Context $context,
        private readonly ResourceConnection $resource
    ) {
        parent::__construct($context);
    }

    public function execute(): Page
    {
        /** @var Page $page */
        $page = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        if (!$this->hasQueuedAccounts()) {
            $page->addHandle('softaware_customerapproval_queue_index_empty');
        }
        $page->setActiveMenu('Softaware_CustomerApproval::queue');
        $page->getConfig()->getTitle()->prepend(__('Customer Approval Queue'));
        return $page;
    }

    /**
     * Same source as the grid (customer grid index).
     */
    private function hasQueuedAccounts(): bool
    {
        try {
            $connection = $this->resource->getConnection();
            $select = $connection->select()
                ->from($this->resource->getTableName('customer_grid_flat'), ['entity_id'])
                ->where(StatusStorage::STATUS_ATTRIBUTE . ' IN (?)', [Approval::STATUS_PENDING, Approval::STATUS_REJECTED])
                ->limit(1);
            return (bool)$connection->fetchOne($select);
        } catch (\Throwable) {
            return true;
        }
    }
}
