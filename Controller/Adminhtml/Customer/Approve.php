<?php
/**
 * CommerceGears Customer Approval
 *
 * @copyright Copyright (c) CommerceGears (https://www.commercegearscommerce.co.uk/)
 */
declare(strict_types=1);

namespace CommerceGears\CustomerApproval\Controller\Adminhtml\Customer;

class Approve extends AbstractAction
{
    protected function apply(int $customerId): void
    {
        $this->approvalManagement->approve($customerId);
        $this->messageManager->addSuccessMessage(__('The customer account was approved.'));
    }
}
