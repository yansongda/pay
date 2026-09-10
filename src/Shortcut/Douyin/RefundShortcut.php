<?php

declare(strict_types=1);

namespace Yansongda\Pay\Shortcut\Douyin;

use Yansongda\Artful\Contract\ShortcutInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Action\DouyinAction;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Douyin\V1\AddRadarPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\ObtainClientTokenPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Refund\AuditPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Refund\RefundPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\ResponsePlugin;

/**
 * 抖音退款：`_action` 分发 `default`（refund_create）/`audit`（refund_audit_callback）.
 */
class RefundShortcut implements ShortcutInterface
{
    /**
     * @param array<string, mixed> $params
     *
     * @return array<class-string>
     *
     * @throws InvalidParamsException
     */
    public function getPlugins(array $params): array
    {
        $action = DouyinAction::tryFrom($params['_action'] ?? DouyinAction::Default->value)
            ?? throw new InvalidParamsException(Exception::PARAMS_SHORTCUT_ACTION_INVALID, '不支持的 _action ['.($params['_action'] ?? '').']');

        return match ($action) {
            DouyinAction::Default => $this->defaultPlugins(),
            DouyinAction::Audit => $this->auditPlugins(),
            default => throw new InvalidParamsException(
                Exception::PARAMS_SHORTCUT_ACTION_INVALID,
                '不支持的 _action ['.($params['_action'] ?? '').']',
            ),
        };
    }

    /**
     * @return array<class-string>
     */
    protected function defaultPlugins(): array
    {
        return [
            StartPlugin::class,
            ObtainClientTokenPlugin::class,
            RefundPlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function auditPlugins(): array
    {
        return [
            StartPlugin::class,
            ObtainClientTokenPlugin::class,
            AuditPlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }
}
