<?php
/**
 * Softaware Customer Approval
 *
 * @copyright Copyright (c) Softaware Commerce (https://www.softawarecommerce.co.uk/)
 */
declare(strict_types=1);

namespace Softaware\CustomerApproval\Block\Adminhtml\Customer;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Softaware\CustomerApproval\Api\ApprovalManagementInterface as Approval;
use Softaware\CustomerApproval\Model\ResourceModel\StatusStorage;

/**
 * Notice above the customer edit form when the account is pending or rejected.
 */
class StatusNotice extends Template
{
    protected $_template = 'Softaware_CustomerApproval::customer/status-notice.phtml';

    public function __construct(
        Context $context,
        private readonly StatusStorage $storage,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getStatus(): ?string
    {
        $customerId = (int)$this->getRequest()->getParam('id');
        if (!$customerId) {
            return null;
        }
        $status = $this->storage->getStatus($customerId);
        return in_array($status, [Approval::STATUS_PENDING, Approval::STATUS_REJECTED], true) ? $status : null;
    }

    public function getReason(): ?string
    {
        return $this->storage->getReason((int)$this->getRequest()->getParam('id'));
    }

    protected function _toHtml(): string
    {
        if (!$this->_authorization->isAllowed('Softaware_CustomerApproval::manage') || $this->getStatus() === null) {
            return '';
        }
        return parent::_toHtml();
    }
}
