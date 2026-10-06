<?php
/**
 * Softaware Customer Approval
 *
 * @copyright Copyright (c) Softaware Commerce (https://www.softawarecommerce.co.uk/)
 */
declare(strict_types=1);

namespace Softaware\CustomerApproval\Controller\Adminhtml\Customer;

class Approve extends AbstractAction
{
    protected function apply(int $customerId): void
    {
        $this->approvalManagement->approve($customerId);
        $this->messageManager->addSuccessMessage(__('The customer account was approved.'));
    }
}
