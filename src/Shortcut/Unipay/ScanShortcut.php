<?php

declare(strict_types=1);

namespace Yansongda\Pay\Shortcut\Unipay;

use Yansongda\Artful\Contract\ShortcutInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Pay\Action\UnipayAction;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Unipay\AddRadarPlugin;
use Yansongda\Pay\Plugin\Unipay\Open\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Unipay\Open\Pay\QrCode\ScanFeePlugin;
use Yansongda\Pay\Plugin\Unipay\Open\Pay\QrCode\ScanPlugin;
use Yansongda\Pay\Plugin\Unipay\Open\Pay\QrCode\ScanPreAuthPlugin;
use Yansongda\Pay\Plugin\Unipay\Open\Pay\QrCode\ScanPreOrderPlugin;
use Yansongda\Pay\Plugin\Unipay\Open\StartPlugin;
use Yansongda\Pay\Plugin\Unipay\Open\VerifySignaturePlugin;

class ScanShortcut implements ShortcutInterface
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
        $action = UnipayAction::tryFrom($params['_action'] ?? UnipayAction::Default->value)
            ?? throw new InvalidParamsException(Exception::PARAMS_SHORTCUT_ACTION_INVALID, '不支持的 _action ['.($params['_action'] ?? '').']');

        return match ($action) {
            UnipayAction::Default => $this->defaultPlugins(),
            UnipayAction::PreAuth => $this->preAuthPlugins(),
            UnipayAction::PreOrder => $this->preOrderPlugins(),
            UnipayAction::Fee => $this->feePlugins(),
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
            ScanPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ParserPlugin::class,
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function preAuthPlugins(): array
    {
        return [
            StartPlugin::class,
            ScanPreAuthPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ParserPlugin::class,
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function preOrderPlugins(): array
    {
        return [
            StartPlugin::class,
            ScanPreOrderPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ParserPlugin::class,
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function feePlugins(): array
    {
        return [
            StartPlugin::class,
            ScanFeePlugin::class,
            AddPayloadSignaturePlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ParserPlugin::class,
        ];
    }
}
