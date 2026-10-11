<?php
/**
 * CommerceGears Customer Approval
 *
 * @copyright Copyright (c) CommerceGears (https://commercegears.com/)
 */
declare(strict_types=1);

namespace CommerceGears\CustomerApproval\Console\Command;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use CommerceGears\CustomerApproval\Api\ApprovalManagementInterface as Approval;
use CommerceGears\CustomerApproval\Model\ResourceModel\StatusStorage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * bin/magento commercegears:customer-approval:status                       list pending accounts
 * bin/magento commercegears:customer-approval:status --status=rejected     list rejected accounts
 * bin/magento commercegears:customer-approval:status 42 --set=approved     approve customer 42 (ID or email)
 */
class StatusCommand extends Command
{
    public function __construct(
        private readonly Approval $approvalManagement,
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly CollectionFactory $collectionFactory,
        private readonly StatusStorage $storage,
        private readonly State $appState
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('commercegears:customer-approval:status')
            ->setDescription('List customer accounts by approval status, or approve / reject / reset one')
            ->addArgument('customer', InputArgument::OPTIONAL, 'Customer ID or email address')
            ->addOption('status', null, InputOption::VALUE_REQUIRED, 'List filter: pending, approved or rejected', Approval::STATUS_PENDING)
            ->addOption('set', null, InputOption::VALUE_REQUIRED, 'New status for the customer: approved, rejected or pending')
            ->addOption('reason', null, InputOption::VALUE_REQUIRED, 'Rejection reason')
            ->addOption('website', null, InputOption::VALUE_REQUIRED, 'Website ID when looking up by email')
            ->addOption('no-email', null, InputOption::VALUE_NONE, 'Do not email the customer');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->appState->setAreaCode(Area::AREA_ADMINHTML);
        } catch (LocalizedException) {
            // Area already set.
        }
        $customer = $input->getArgument('customer');
        if ($customer === null) {
            return $this->listCustomers((string)$input->getOption('status'), $output);
        }
        try {
            $customerId = $this->resolveCustomerId((string)$customer, $input->getOption('website'));
            $set = $input->getOption('set');
            $notify = !$input->getOption('no-email');
            match ($set) {
                null => null,
                Approval::STATUS_APPROVED => $this->approvalManagement->approve($customerId, $notify),
                Approval::STATUS_REJECTED => $this->approvalManagement->reject(
                    $customerId,
                    $input->getOption('reason') !== null ? (string)$input->getOption('reason') : null,
                    $notify
                ),
                Approval::STATUS_PENDING => $this->approvalManagement->setPending($customerId),
                default => throw new LocalizedException(__('--set must be approved, rejected or pending.')),
            };
            $output->writeln(sprintf(
                'Customer %d: %s',
                $customerId,
                $this->approvalManagement->getStatus($customerId)
            ));
        } catch (LocalizedException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }

    private function resolveCustomerId(string $value, mixed $websiteId): int
    {
        if (ctype_digit($value)) {
            return (int)$this->customerRepository->getById((int)$value)->getId();
        }
        return (int)$this->customerRepository->get($value, $websiteId !== null ? (int)$websiteId : null)->getId();
    }

    private function listCustomers(string $status, OutputInterface $output): int
    {
        if (!in_array($status, [Approval::STATUS_PENDING, Approval::STATUS_APPROVED, Approval::STATUS_REJECTED], true)) {
            $output->writeln('<error>--status must be pending, approved or rejected.</error>');
            return Command::INVALID;
        }
        $collection = $this->collectionFactory->create()
            ->addAttributeToSelect(['firstname', 'lastname', StatusStorage::REASON_ATTRIBUTE])
            ->addAttributeToFilter(StatusStorage::STATUS_ATTRIBUTE, $status)
            ->setOrder('created_at', 'DESC');
        $table = new Table($output);
        $table->setHeaders(['ID', 'Email', 'Name', 'Website', 'Registered', 'Reason']);
        foreach ($collection as $customer) {
            $table->addRow([
                $customer->getId(),
                $customer->getEmail(),
                trim($customer->getFirstname() . ' ' . $customer->getLastname()),
                $customer->getWebsiteId(),
                $customer->getCreatedAt(),
                (string)$customer->getData(StatusStorage::REASON_ATTRIBUTE),
            ]);
        }
        $table->render();
        $output->writeln(sprintf('%d %s account(s).', $collection->getSize(), $status));
        return Command::SUCCESS;
    }
}
