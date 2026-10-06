<?php
/**
 * Softaware Customer Approval
 *
 * @copyright Copyright (c) Softaware Commerce (https://www.softawarecommerce.co.uk/)
 */
declare(strict_types=1);

namespace Softaware\CustomerApproval\Plugin\Admin;

use Magento\Backend\Block\AnchorRenderer;
use Magento\Backend\Block\Menu;
use Magento\Backend\Model\Menu\Item;
use Magento\Framework\AuthorizationInterface;
use Softaware\CustomerApproval\Model\PendingCounter;

/**
 * Shows the number of accounts waiting for approval next to the "Approval Queue" menu item.
 * The admin menu HTML is cached per user, so the count is added to its cache key.
 */
class MenuCountPlugin
{
    public const MENU_ID = 'Softaware_CustomerApproval::queue';

    public function __construct(
        private readonly PendingCounter $pendingCounter,
        private readonly AuthorizationInterface $authorization
    ) {
    }

    /**
     * @param array<int|string, mixed> $result
     * @return array<int|string, mixed>
     */
    public function afterGetCacheKeyInfo(Menu $subject, array $result): array
    {
        if ($this->authorization->isAllowed('Softaware_CustomerApproval::manage')) {
            $result['softaware_customerapproval_pending'] = $this->pendingCounter->get();
        }
        return $result;
    }

    public function afterRenderAnchor(AnchorRenderer $subject, string $result, $activeItem, Item $menuItem, $level): string
    {
        if ($menuItem->getId() !== self::MENU_ID) {
            return $result;
        }
        $count = $this->pendingCounter->get();
        if ($count <= 0) {
            return $result;
        }
        $pos = strrpos($result, '</span>');
        if ($pos === false) {
            return $result;
        }
        return substr($result, 0, $pos) . ' (' . $count . ')' . substr($result, $pos);
    }
}
