<?php
/**
 * CommerceGears Customer Approval
 *
 * @copyright Copyright (c) CommerceGears (https://commercegears.com/)
 */
declare(strict_types=1);

namespace CommerceGears\CustomerApproval\Model\ResourceModel;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\App\ResourceConnection;

/**
 * Reads and writes the approval attributes directly in the customer EAV table. Used on every login check,
 * so it is a single indexed query instead of a full customer load, and status changes never trigger the
 * customer save validation (which can fail on incomplete legacy customers).
 */
class StatusStorage
{
    public const STATUS_ATTRIBUTE = 'commercegears_approval_status';
    public const REASON_ATTRIBUTE = 'commercegears_approval_reason';

    /** @var array<string, int> */
    private array $attributeIds = [];

    /** @var array<int, string|null> */
    private array $cache = [];

    public function __construct(
        private readonly ResourceConnection $resource,
        private readonly EavConfig $eavConfig
    ) {
    }

    /**
     * Stored status, or null for customers that never got one (created before the module, by import, ...).
     */
    public function getStatus(int $customerId): ?string
    {
        if (!array_key_exists($customerId, $this->cache)) {
            $value = $this->readValue($customerId, self::STATUS_ATTRIBUTE);
            $this->cache[$customerId] = $value === null || $value === '' ? null : $value;
        }
        return $this->cache[$customerId];
    }

    public function getReason(int $customerId): ?string
    {
        $value = $this->readValue($customerId, self::REASON_ATTRIBUTE);
        return $value === '' ? null : $value;
    }

    public function save(int $customerId, string $status, ?string $reason = null): void
    {
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('customer_entity_varchar');
        $connection->insertOnDuplicate(
            $table,
            ['attribute_id' => $this->getAttributeId(self::STATUS_ATTRIBUTE), 'entity_id' => $customerId, 'value' => $status],
            ['value']
        );
        $reasonId = $this->getAttributeId(self::REASON_ATTRIBUTE);
        $reason = $reason !== null ? mb_substr(trim($reason), 0, 255) : '';
        if ($reason === '') {
            $connection->delete($table, ['attribute_id = ?' => $reasonId, 'entity_id = ?' => $customerId]);
        } else {
            $connection->insertOnDuplicate(
                $table,
                ['attribute_id' => $reasonId, 'entity_id' => $customerId, 'value' => $reason],
                ['value']
            );
        }
        $this->cache[$customerId] = $status;
    }

    public function countByStatus(string $status): int
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from(['v' => $this->resource->getTableName('customer_entity_varchar')], ['COUNT(*)'])
            ->where('v.attribute_id = ?', $this->getAttributeId(self::STATUS_ATTRIBUTE))
            ->where('v.value = ?', $status);
        return (int)$connection->fetchOne($select);
    }

    /**
     * @param int[] $customerIds
     * @return array<int, string> customer id => status, only for customers that have one
     */
    public function getStatuses(array $customerIds): array
    {
        if ($customerIds === []) {
            return [];
        }
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from($this->resource->getTableName('customer_entity_varchar'), ['entity_id', 'value'])
            ->where('attribute_id = ?', $this->getAttributeId(self::STATUS_ATTRIBUTE))
            ->where('entity_id IN (?)', array_map('intval', $customerIds));
        return array_map('strval', $connection->fetchPairs($select));
    }

    public function getAttributeId(string $code): int
    {
        if (!isset($this->attributeIds[$code])) {
            $this->attributeIds[$code] = (int)$this->eavConfig
                ->getAttribute(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER, $code)
                ->getAttributeId();
        }
        return $this->attributeIds[$code];
    }

    public function clearCache(?int $customerId = null): void
    {
        if ($customerId === null) {
            $this->cache = [];
        } else {
            unset($this->cache[$customerId]);
        }
    }

    private function readValue(int $customerId, string $code): ?string
    {
        $attributeId = $this->getAttributeId($code);
        if (!$attributeId || !$customerId) {
            return null;
        }
        $connection = $this->resource->getConnection();
        $value = $connection->fetchOne(
            $connection->select()
                ->from($this->resource->getTableName('customer_entity_varchar'), ['value'])
                ->where('attribute_id = ?', $attributeId)
                ->where('entity_id = ?', $customerId)
        );
        return $value === false ? null : (string)$value;
    }
}
