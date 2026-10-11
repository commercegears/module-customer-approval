<?php
/**
 * CommerceGears Customer Approval
 *
 * @copyright Copyright (c) CommerceGears (https://commercegears.com/)
 */
declare(strict_types=1);

namespace CommerceGears\CustomerApproval\Controller\Adminhtml\Customer;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use CommerceGears\CustomerApproval\Api\ApprovalManagementInterface;

/**
 * Mass approve / reject from the customer grid and the approval queue.
 */
abstract class AbstractMassAction extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'CommerceGears_CustomerApproval::manage';

    public function __construct(
        Context $context,
        private readonly Filter $filter,
        private readonly CollectionFactory $collectionFactory,
        protected readonly ApprovalManagementInterface $approvalManagement
    ) {
        parent::__construct($context);
    }

    abstract protected function apply(int $customerId): void;

    abstract protected function getSuccessMessage(int $count): string;

    public function execute(): Redirect
    {
        $done = 0;
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            foreach ($collection->getAllIds() as $customerId) {
                try {
                    $this->apply((int)$customerId);
                    $done++;
                } catch (LocalizedException $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                }
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        if ($done) {
            $this->messageManager->addSuccessMessage($this->getSuccessMessage($done));
        }
        $redirect = $this->resultRedirectFactory->create();
        if ($this->getRequest()->getParam('namespace') === 'commercegears_customerapproval_queue_listing') {
            return $redirect->setPath('commercegears_customerapproval/queue/index');
        }
        return $redirect->setPath('customer/index/index');
    }
}
