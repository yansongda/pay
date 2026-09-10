<?php

declare(strict_types=1);

namespace Yansongda\Pay\Shortcut\Alipay;

use Yansongda\Artful\Contract\ShortcutInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Pay\Action\AlipayCancelAction;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Alipay\V2\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Alipay\V2\AddRadarPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\FormatPayloadBizContentPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Agreement\Pay\CancelPlugin as AgreementCancelPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Authorization\Auth\CancelPlugin as AuthorizationCancelPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Mini\CancelPlugin as MiniCancelPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Pos\CancelPlugin as PosCancelPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Scan\CancelPlugin as ScanCancelPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\ResponsePlugin;
use Yansongda\Pay\Plugin\Alipay\V2\StartPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\VerifySignaturePlugin;

class CancelShortcut implements ShortcutInterface
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
        $action = AlipayCancelAction::tryFrom($params['_action'] ?? AlipayCancelAction::Default->value)
            ?? throw new InvalidParamsException(
                Exception::PARAMS_SHORTCUT_ACTION_INVALID,
                '不支持的 _action ['.($params['_action'] ?? '').']',
            );

        return match ($action) {
            AlipayCancelAction::Default => $this->defaultPlugins(),
            AlipayCancelAction::Agreement => $this->agreementPlugins(),
            AlipayCancelAction::Authorization => $this->authorizationPlugins(),
            AlipayCancelAction::Mini => $this->miniPlugins(),
            AlipayCancelAction::Pos => $this->posPlugins(),
            AlipayCancelAction::Scan => $this->scanPlugins(),
        };
    }

    /**
     * @return array<class-string>
     */
    protected function defaultPlugins(): array
    {
        return $this->posPlugins();
    }

    /**
     * @return array<class-string>
     */
    protected function agreementPlugins(): array
    {
        return [
            StartPlugin::class,
            AgreementCancelPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function authorizationPlugins(): array
    {
        return [
            StartPlugin::class,
            AuthorizationCancelPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function miniPlugins(): array
    {
        return [
            StartPlugin::class,
            MiniCancelPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function posPlugins(): array
    {
        return [
            StartPlugin::class,
            PosCancelPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function scanPlugins(): array
    {
        return [
            StartPlugin::class,
            ScanCancelPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }
}
