<?php

declare(strict_types=1);

namespace Yansongda\Pay\Shortcut\Wechat;

use Yansongda\Artful\Contract\ShortcutInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Action\WechatPapayAction;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\AddRadarPlugin;
use Yansongda\Pay\Plugin\Wechat\ResponsePlugin;
use Yansongda\Pay\Plugin\Wechat\V2\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Wechat\V2\Papay\Direct\ApplyPlugin;
use Yansongda\Pay\Plugin\Wechat\V2\Papay\Direct\ContractOrderPlugin;
use Yansongda\Pay\Plugin\Wechat\V2\Papay\Direct\MiniOnlyContractPlugin;
use Yansongda\Pay\Plugin\Wechat\V2\Pay\App\InvokePlugin as AppInvokePlugin;
use Yansongda\Pay\Plugin\Wechat\V2\Pay\Mini\InvokePlugin as MiniInvokePlugin;
use Yansongda\Pay\Plugin\Wechat\V2\Pay\Mp\InvokePlugin as MpInvokePlugin;
use Yansongda\Pay\Plugin\Wechat\V2\VerifySignaturePlugin;

class PapayShortcut implements ShortcutInterface
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
        $action = WechatPapayAction::tryFrom($params['_action'] ?? WechatPapayAction::Default->value)
            ?? throw new InvalidParamsException(
                Exception::PARAMS_SHORTCUT_ACTION_INVALID,
                '不支持的 _action ['.($params['_action'] ?? '').']',
            );

        return match ($action) {
            WechatPapayAction::Default, WechatPapayAction::Order => $this->orderPlugins($params),
            WechatPapayAction::Contract => $this->contractPlugins($params),
            WechatPapayAction::Apply => $this->applyPlugins(),
        };
    }

    /**
     * @param array<string, mixed> $params
     *
     * @return array<class-string>
     *
     * @throws InvalidParamsException
     */
    protected function defaultPlugins(array $params): array
    {
        return $this->orderPlugins($params);
    }

    /**
     * @param array<string, mixed> $params
     *
     * @return array<class-string>
     *
     * @throws InvalidParamsException
     */
    protected function orderPlugins(array $params): array
    {
        $plugins = [
            StartPlugin::class,
            ContractOrderPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
        ];

        if (null !== ($invoke = $this->getInvoke($params))) {
            $plugins[] = $invoke;
        }

        return [...$plugins, VerifySignaturePlugin::class, ResponsePlugin::class, ParserPlugin::class];
    }

    /**
     * @param array<string, mixed> $params
     *
     * @return array<class-string>
     *
     * @throws InvalidParamsException
     */
    protected function contractPlugins(array $params): array
    {
        return match ($params['_type'] ?? 'default') {
            'mini' => [StartPlugin::class, MiniOnlyContractPlugin::class, AddPayloadSignaturePlugin::class],
            default => throw new InvalidParamsException(Exception::PARAMS_WECHAT_PAPAY_TYPE_NOT_SUPPORTED, '参数异常: 微信扣关服务纯签约，当前传递的 `_type` 类型不支持')
        };
    }

    /**
     * @return array<class-string>
     */
    protected function applyPlugins(): array
    {
        return [
            StartPlugin::class,
            ApplyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    /**
     * @param array<string, mixed> $params
     *
     * @throws InvalidParamsException
     */
    protected function getInvoke(array $params): ?string
    {
        return match ($params['_type'] ?? 'default') {
            'app' => AppInvokePlugin::class,
            'mini' => MiniInvokePlugin::class,
            'mp' => MpInvokePlugin::class,
            'scan', 'h5' => null, // NATIVE 响应 code_url，MWEB 响应 mweb_url，均无需调起插件
            default => throw new InvalidParamsException(Exception::PARAMS_WECHAT_PAPAY_TYPE_NOT_SUPPORTED, '参数异常: 微信扣关服务支付中签约，当前传递的 `_type` 类型不支持')
        };
    }
}
