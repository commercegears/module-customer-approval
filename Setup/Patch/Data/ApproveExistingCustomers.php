<?php
/**
 * CommerceGears Customer Approval
 *
 * @copyright Copyright (c) CommerceGears (https://commercegears.com/)
 */
declare(strict_types=1);

namespace CommerceGears\CustomerApproval\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use CommerceGears\CustomerApproval\Api\ApprovalManagementInterface as Approval;
use CommerceGears\CustomerApproval\Model\ResourceModel\StatusStorage;

/**
 * Customers that exist when the module is installed are approved (one INSERT ... SELECT, no per-row work).
 */
class ApproveExistingCustomers implements DataPatchInterface
{
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly EavConfig $eavConfig,
        private readonly IndexerRegistry $indexerRegistry
    ) {
    }

    public function apply(): self
    {
        $connection = $this->moduleDataSetup->getConnection();
        $this->eavConfig->clear();
        $attributeId = (int)$this->eavConfig->getAttribute(Customer::ENTITY, StatusStorage::STATUS_ATTRIBUTE)->getId();
        if ($attributeId) {
            $select = $connection->select()->from(
                $this->moduleDataSetup->getTable('customer_entity'),
                [
                    'attribute_id' => new \Zend_Db_Expr((string)$attributeId),
                    'entity_id' => 'entity_id',
                    'value' => new \Zend_Db_Expr($connection->quote(Approval::STATUS_APPROVED)),
                ]
            );
            $connection->query($connection->insertFromSelect(
                $select,
                $this->moduleDataSetup->getTable('customer_entity_varchar'),
                ['attribute_id', 'entity_id', 'value'],
                AdapterInterface::INSERT_IGNORE
            ));
        }
        // The customer grid needs a full reindex to get the new column.
        $this->indexerRegistry->get(Customer::CUSTOMER_GRID_INDEXER_ID)->invalidate();
        return $this;
    }

    public static function getDependencies(): array
    {
        return [AddApprovalAttributes::class];
    }

    public function getAliases(): array
    {
        return [];
    }
}
