<?php
/**
 * Softaware Customer Approval
 *
 * @copyright Copyright (c) Softaware Commerce (https://www.softawarecommerce.co.uk/)
 */
declare(strict_types=1);

namespace Softaware\CustomerApproval\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Softaware\CustomerApproval\Model\Attribute\Source\Status;
use Softaware\CustomerApproval\Model\ResourceModel\StatusStorage;

/**
 * Customer attributes for the approval status (customer grid column + filter) and the rejection reason.
 * Not part of any form: they are only changed through the approval actions.
 */
class AddApprovalAttributes implements DataPatchInterface, PatchRevertableInterface
{
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly CustomerSetupFactory $customerSetupFactory,
        private readonly AttributeSetFactory $attributeSetFactory
    ) {
    }

    public function apply(): self
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $entityType = $customerSetup->getEavConfig()->getEntityType(Customer::ENTITY);
        $attributeSetId = (int)$entityType->getDefaultAttributeSetId();
        $attributeGroupId = (int)$this->attributeSetFactory->create()->getDefaultGroupId($attributeSetId);

        $customerSetup->addAttribute(Customer::ENTITY, StatusStorage::STATUS_ATTRIBUTE, [
            'type' => 'varchar',
            'label' => 'Approval Status',
            'input' => 'select',
            'source' => Status::class,
            'required' => false,
            'visible' => false,
            'user_defined' => true,
            'system' => false,
            'position' => 1000,
            'is_used_in_grid' => true,
            'is_visible_in_grid' => true,
            'is_filterable_in_grid' => true,
            'is_searchable_in_grid' => false,
        ]);
        $customerSetup->addAttribute(Customer::ENTITY, StatusStorage::REASON_ATTRIBUTE, [
            'type' => 'varchar',
            'label' => 'Rejection Reason',
            'input' => 'text',
            'required' => false,
            'visible' => false,
            'user_defined' => true,
            'system' => false,
            'position' => 1001,
            'is_used_in_grid' => true,
            'is_visible_in_grid' => false,
            'is_filterable_in_grid' => false,
            'is_searchable_in_grid' => false,
        ]);
        foreach ([StatusStorage::STATUS_ATTRIBUTE, StatusStorage::REASON_ATTRIBUTE] as $code) {
            $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, $code);
            $attribute->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => [],
            ]);
            $attribute->save();
        }
        $this->moduleDataSetup->getConnection()->endSetup();
        return $this;
    }

    public function revert(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerSetup->removeAttribute(Customer::ENTITY, StatusStorage::STATUS_ATTRIBUTE);
        $customerSetup->removeAttribute(Customer::ENTITY, StatusStorage::REASON_ATTRIBUTE);
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
