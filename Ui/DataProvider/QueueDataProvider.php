<?php
/**
 * CommerceGears Customer Approval
 *
 * @copyright Copyright (c) CommerceGears (https://www.commercegearscommerce.co.uk/)
 */
declare(strict_types=1);

namespace CommerceGears\CustomerApproval\Ui\DataProvider;

use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;
use CommerceGears\CustomerApproval\Api\ApprovalManagementInterface as Approval;
use CommerceGears\CustomerApproval\Model\ResourceModel\StatusStorage;

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
