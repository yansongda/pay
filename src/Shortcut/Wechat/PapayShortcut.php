<?php

declare(strict_types=1);

namespace Yansongda\Pay\Shortcut\Wechat;

use Yansongda\Artful\Contract\ShortcutInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\AddRadarPlugin;
use Yansongda\Pay\Plugin\Wechat\ResponsePlugin;
use Yansongda\Pay\Plugin\Wechat\StartPlugin;
use Yansongda\Pay\Plugin\Wechat\V2\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Wechat\V2\Papay\Direct\ApplyPlugin;
use Yansongda\Pay\Plugin\Wechat\V2\Papay\Direct\ContractOrderPlugin;
use Yansongda\Pay\Plugin\Wechat\V2\Papay\Direct\MiniOnlyContractPlugin;
use Yansongda\Pay\Plugin\Wechat\V2\Pay\App\InvokePlugin as AppInvokePlugin;
use Yansongda\Pay\Plugin\Wechat\V2\Pay\Mini\InvokePlugin as MiniInvokePlugin;
use Yansongda\Pay\Plugin\Wechat\V2\VerifySignaturePlugin;
use Yansongda\Supports\Str;

class PapayShortcut implements ShortcutInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function getPlugins(array $params): array
    {
        $action = Str::camel($params['_action'] ?? 'default').'Plugins';

        if (method_exists($this, $action)) {
            return $this->{$action}($params);
        }

        throw new InvalidParamsException(Exception::PARAMS_SHORTCUT_ACTION_INVALID, "Papay action [{$action}] not supported");
    }

    /**
     * @throws InvalidParamsException
     */
    protected function defaultPlugins(array $params): array
    {
        return $this->orderPlugins($params);
    }

    /**
     * @throws InvalidParamsException
     */
    protected function orderPlugins(array $params): array
    {
        return [
            StartPlugin::class,
            ContractOrderPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            $this->getInvoke($params),
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    /**
     * @throws InvalidParamsException
     */
    protected function contractPlugins(array $params): array
    {
        return match ($params['_type'] ?? 'default') {
            'mini' => [StartPlugin::class, MiniOnlyContractPlugin::class, AddPayloadSignaturePlugin::class],
            default => throw new InvalidParamsException(Exception::PARAMS_WECHAT_PAPAY_TYPE_NOT_SUPPORTED, '参数异常: 微信扣关服务纯签约，当前传递的 `_type` 类型不支持')
        };
    }

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
     * @throws InvalidParamsException
     */
    protected function getInvoke(array $params): string
    {
        return match ($params['_type'] ?? 'default') {
            'app' => AppInvokePlugin::class,
            'mini' => MiniInvokePlugin::class,
            default => throw new InvalidParamsException(Exception::PARAMS_WECHAT_PAPAY_TYPE_NOT_SUPPORTED, '参数异常: 微信扣关服务支付中签约，当前传递的 `_type` 类型不支持')
        };
    }
}
