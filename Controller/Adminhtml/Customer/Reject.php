<?php
/**
 * Softaware Customer Approval
 *
 * @copyright Copyright (c) Softaware Commerce (https://www.softawarecommerce.co.uk/)
 */
declare(strict_types=1);

namespace Softaware\CustomerApproval\Controller\Adminhtml\Customer;

class Reject extends AbstractAction
{
    protected function apply(int $customerId): void
    {
        $reason = trim((string)$this->getRequest()->getParam('reason'));
        $this->approvalManagement->reject($customerId, $reason !== '' ? mb_substr($reason, 0, 255) : null);
        $this->messageManager->addSuccessMessage(__('The customer account was rejected.'));
    }
}
