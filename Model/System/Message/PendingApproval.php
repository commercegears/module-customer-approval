<?php
/**
 * Softaware Customer Approval
 *
 * @copyright Copyright (c) Softaware Commerce (https://www.softawarecommerce.co.uk/)
 */
declare(strict_types=1);

namespace Softaware\CustomerApproval\Model\System\Message;

use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\UrlInterface;
use Softaware\CustomerApproval\Model\Config;
use Softaware\CustomerApproval\Model\PendingCounter;

/**
 * Admin notice bar: "N customer accounts are waiting for approval".
 */
class PendingApproval implements MessageInterface
{
    public function __construct(
        private readonly PendingCounter $pendingCounter,
        private readonly Config $config,
        private readonly AuthorizationInterface $authorization,
        private readonly UrlInterface $urlBuilder,
        private readonly Escaper $escaper
    ) {
    }

    public function getIdentity(): string
    {
        return 'softaware_customerapproval_pending';
    }

    public function isDisplayed(): bool
    {
        return $this->authorization->isAllowed('Softaware_CustomerApproval::manage')
            && $this->config->isEnabledAnywhere()
            && $this->pendingCounter->get() > 0;
    }

    public function getText(): string
    {
        $count = $this->pendingCounter->get();
        $url = $this->escaper->escapeUrl($this->urlBuilder->getUrl('softaware_customerapproval/queue/index'));
        if ($count === 1) {
            return (string)__('1 customer account is waiting for approval. <a href="%1">Review it in the approval queue</a>.', $url);
        }
        return (string)__(
            '%1 customer accounts are waiting for approval. <a href="%2">Review them in the approval queue</a>.',
            $count,
            $url
        );
    }

    public function getSeverity(): int
    {
        return self::SEVERITY_NOTICE;
    }
}
