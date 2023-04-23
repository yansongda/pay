<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Shortcut;

use Yansongda\Pay\Contract\ShortcutInterface;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Wechat\Papay\ApplyPlugin;
use Yansongda\Pay\Plugin\Wechat\Papay\ContractOrderPlugin;
use Yansongda\Pay\Plugin\Wechat\Papay\OnlyContractPlugin;
use Yansongda\Pay\Plugin\Wechat\Pay\Common\InvokePrepayV2Plugin;
use Yansongda\Pay\Plugin\Wechat\PreparePlugin;
use Yansongda\Pay\Plugin\Wechat\RadarSignPlugin;
use Yansongda\Supports\Str;

class PapayShortcut implements ShortcutInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function getPlugins(array $params): array
    {
        $typeMethod = Str::camel($params['_action'] ?? 'default').'Plugins';

        if (method_exists($this, $typeMethod)) {
            return $this->{$typeMethod}($params);
        }

        throw new InvalidParamsException(Exception::SHORTCUT_MULTI_ACTION_ERROR, "Papay action [{$typeMethod}] not supported");
    }

    /**
     * 返回只签约（委托代扣）参数.
     *
     * @see https://pay.weixin.qq.com/wiki/doc/api/wxpay_v2/papay/chapter3_3.shtml
     */
    public function ContractPlugins(): array
    {
        return [
            PreparePlugin::class,
            OnlyContractPlugin::class,
        ];
    }

    /**
     * 申请代扣.
     *
     * @see https://pay.weixin.qq.com/wiki/doc/api/wxpay_v2/papay/chapter3_8.shtml
     */
    public function applyPlugins(): array
    {
        return [
            PreparePlugin::class,
            ApplyPlugin::class,
            RadarSignPlugin::class,
            ParserPlugin::class,
        ];
    }

    /**
     * 支付中签约.
     *
     * @see https://pay.weixin.qq.com/wiki/doc/api/wxpay_v2/papay/chapter3_5.shtml
     */
    protected function defaultPlugins(array $params): array
    {
        return [
            PreparePlugin::class,
            ContractOrderPlugin::class,
            RadarSignPlugin::class,
            $this->getInvoke($params),
            ParserPlugin::class,
        ];
    }

    protected function getInvoke(array $params): string
    {
        switch ($params['_type'] ?? 'default') {
            case 'app':
                return \Yansongda\Pay\Plugin\Wechat\Pay\App\InvokePrepayV2Plugin::class;

            case 'mini':
                return \Yansongda\Pay\Plugin\Wechat\Pay\Mini\InvokePrepayV2Plugin::class;

            default:
                return InvokePrepayV2Plugin::class;
        }
    }
}
