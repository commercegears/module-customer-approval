<?php
/**
 * CommerceGears Customer Approval
 *
 * @copyright Copyright (c) CommerceGears (https://www.commercegearscommerce.co.uk/)
 */
declare(strict_types=1);

namespace CommerceGears\CustomerApproval\Controller\Adminhtml\Customer;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use CommerceGears\CustomerApproval\Api\ApprovalManagementInterface;

/**
 * Single-customer approve / reject / set pending, posted from the customer edit page buttons.
 */
abstract class AbstractAction extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'CommerceGears_CustomerApproval::manage';

    public function __construct(
        Context $context,
        protected readonly ApprovalManagementInterface $approvalManagement
    ) {
        parent::__construct($context);
    }

    abstract protected function apply(int $customerId): void;

    public function execute(): Redirect
    {
        $customerId = (int)$this->getRequest()->getParam('id');
        try {
            $this->apply($customerId);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        $redirect = $this->resultRedirectFactory->create();
        if ($this->getRequest()->getParam('back') === 'queue') {
            return $redirect->setPath('commercegears_customerapproval/queue/index');
        }
        return $redirect->setPath('customer/index/edit', ['id' => $customerId]);
    }
}
