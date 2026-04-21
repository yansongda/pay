<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\Marketing\ECommerceRefund;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\WechatConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Traits\WechatTrait;

/**
 * @see https://pay.weixin.qq.com/docs/partner/apis/ecommerce-refund/refunds/query-return-advance.html
 */
class QueryReturnAdvancePlugin implements PluginInterface
{
    use WechatTrait;

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][V3][Marketing][ECommerceRefund][QueryReturnAdvancePlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $payload = $rocket->getPayload();

        /** @var WechatConfig $config */
        $config = self::getProviderConfig('wechat', $params);
        $refundId = $payload?->get('refund_id') ?? null;

        if (Pay::MODE_NORMAL === $config->getMode()) {
            throw new InvalidParamsException(Exception::PARAMS_PLUGIN_ONLY_SUPPORT_SERVICE_MODE, '参数异常: 平台收付通（退款）-查询垫付回补结果，只支持服务商模式，当前配置为普通商户模式');
        }

        if (is_null($refundId)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 平台收付通（退款）-查询垫付回补结果，缺少必要参数 `refund_id`');
        }

        $rocket->setPayload([
            '_method' => 'GET',
            '_service_url' => 'v3/ecommerce/refunds/'.$refundId.'/return-advance?sub_mchid='.$payload->get('sub_mchid', $config->getSubMchId() ?? ''),
        ]);

        Logger::info('[Wechat][V3][Marketing][ECommerceRefund][QueryReturnAdvancePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
