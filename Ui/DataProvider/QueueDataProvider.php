<?php
/**
 * Softaware Customer Approval
 *
 * @copyright Copyright (c) Softaware Commerce (https://www.softawarecommerce.co.uk/)
 */
declare(strict_types=1);

namespace Softaware\CustomerApproval\Ui\DataProvider;

use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;
use Softaware\CustomerApproval\Api\ApprovalManagementInterface as Approval;
use Softaware\CustomerApproval\Model\ResourceModel\StatusStorage;

/**
 * Approval queue: the customer grid index limited to pending and rejected accounts.
 */
class QueueDataProvider extends DataProvider
{
    private bool $queueFilterAdded = false;

    public function getSearchCriteria()
    {
        if (!$this->queueFilterAdded) {
            $this->queueFilterAdded = true;
            $this->addFilter(
                $this->filterBuilder
                    ->setField(StatusStorage::STATUS_ATTRIBUTE)
                    ->setConditionType('in')
                    ->setValue([Approval::STATUS_PENDING, Approval::STATUS_REJECTED])
                    ->create()
            );
        }
        return parent::getSearchCriteria();
    }
}
