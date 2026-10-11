<?php
/**
 * CommerceGears Customer Approval
 *
 * @copyright Copyright (c) CommerceGears (https://commercegears.com/)
 */
declare(strict_types=1);

namespace CommerceGears\CustomerApproval\Controller\Adminhtml\Customer;

class MassReject extends AbstractMassAction
{
    protected function apply(int $customerId): void
    {
        $this->approvalManagement->reject($customerId);
    }

    protected function getSuccessMessage(int $count): string
    {
        return $count === 1
            ? (string)__('1 customer account rejected.')
            : (string)__('%1 customer accounts rejected.', $count);
    }
}
