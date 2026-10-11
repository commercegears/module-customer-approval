<?php
/**
 * CommerceGears Customer Approval
 *
 * @copyright Copyright (c) CommerceGears (https://www.commercegearscommerce.co.uk/)
 */
declare(strict_types=1);

namespace CommerceGears\CustomerApproval\Model;

use Magento\Framework\App\CacheInterface;
use CommerceGears\CustomerApproval\Api\ApprovalManagementInterface as Approval;
use CommerceGears\CustomerApproval\Model\ResourceModel\StatusStorage;

/**
 * Number of accounts waiting for approval, cached for 5 minutes (shown in the admin menu and notice bar).
 */
class PendingCounter
{
    private const CACHE_KEY = 'commercegears_customerapproval_pending_count';
    private const CACHE_LIFETIME = 300;

    private ?int $count = null;

    public function __construct(
        private readonly StatusStorage $storage,
        private readonly CacheInterface $cache
    ) {
    }

    public function get(): int
    {
        if ($this->count === null) {
            $cached = $this->cache->load(self::CACHE_KEY);
            if ($cached !== false && $cached !== null && ctype_digit((string)$cached)) {
                $this->count = (int)$cached;
            } else {
                $this->count = $this->storage->countByStatus(Approval::STATUS_PENDING);
                $this->cache->save((string)$this->count, self::CACHE_KEY, [], self::CACHE_LIFETIME);
            }
        }
        return $this->count;
    }

    public function invalidate(): void
    {
        $this->count = null;
        $this->cache->remove(self::CACHE_KEY);
    }
}
