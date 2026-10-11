<?php
/**
 * CommerceGears Customer Approval
 *
 * @copyright Copyright (c) CommerceGears (https://www.commercegearscommerce.co.uk/)
 */
declare(strict_types=1);

namespace CommerceGears\CustomerApproval\Block\Adminhtml\Customer;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use CommerceGears\CustomerApproval\Api\ApprovalManagementInterface as Approval;
use CommerceGears\CustomerApproval\Model\ResourceModel\StatusStorage;

/**
 * Notice above the customer edit form when the account is pending or rejected.
 */
class StatusNotice extends Template
{
    protected $_template = 'CommerceGears_CustomerApproval::customer/status-notice.phtml';

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
        if (!$this->_authorization->isAllowed('CommerceGears_CustomerApproval::manage') || $this->getStatus() === null) {
            return '';
        }
        return parent::_toHtml();
    }
}
