<?php
/**
 * CommerceGears Customer Approval
 *
 * @copyright Copyright (c) CommerceGears (https://commercegears.com/)
 */
declare(strict_types=1);

namespace CommerceGears\CustomerApproval\Controller\Adminhtml\Customer;

class Reject extends AbstractAction
{
    protected function apply(int $customerId): void
    {
        $reason = trim((string)$this->getRequest()->getParam('reason'));
        $this->approvalManagement->reject($customerId, $reason !== '' ? mb_substr($reason, 0, 255) : null);
        $this->messageManager->addSuccessMessage(__('The customer account was rejected.'));
    }
}
