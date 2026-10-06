<?php
/**
 * Softaware Customer Approval
 *
 * @copyright Copyright (c) Softaware Commerce (https://www.softawarecommerce.co.uk/)
 */
declare(strict_types=1);

namespace Softaware\CustomerApproval\Controller\Adminhtml\Customer;

class MassReject extends AbstractMassAction
{
    protected function apply(int $customerId): void
    {
        $this->approvalManagement->reject($customerId);
    }

    protected function getSuccessMessage(int $count): string
    {
        return (string)__('%1 customer account(s) rejected.', $count);
    }
}
