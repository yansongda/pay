<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\Pay\Pos;

use Closure;
use Throwable;
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
use Yansongda\Supports\Collection;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/code-payment-v3/direct/reverse.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/partner-code-payment-v3/partner/partner-reverse.html
 */
class CancelPlugin implements PluginInterface
{
    use WechatTrait;

    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     * @throws Throwable                随机字符串生成失败
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][V3][Pay][Pos][CancelPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();

        /** @var WechatConfig $config */
        $config = self::getProviderConfig('wechat', $params);
        $payload = $rocket->getPayload();

        $outTradeNo = $payload?->get('out_trade_no') ?? null;

        if (empty($outTradeNo)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 付款码支付撤销订单，参数缺少 `out_trade_no`');
        }

        if (Pay::MODE_SERVICE === $config->getMode()) {
            $data = $this->service($payload, $params, $config);
        }

        $rocket->setPayload(array_merge(
            [
                '_method' => 'POST',
                '_url' => 'v3/pay/transactions/out-trade-no/'.$outTradeNo.'/reverse',
                '_service_url' => 'v3/pay/partner/transactions/out-trade-no/'.$outTradeNo.'/reverse',
            ],
            $data ?? $this->normal($params, $config)
        ));

        Logger::info('[Wechat][V3][Pay][Pos][CancelPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function normal(array $params, WechatConfig $config): array
    {
        return [
            'appid' => $config->getAppIdByType($params['_type'] ?? 'mp') ?? '',
            'mchid' => $config->getMchId(),
        ];
    }

    protected function service(Collection $payload, array $params, WechatConfig $config): array
    {
        return [
            'sp_appid' => $config->getAppIdByType($params['_type'] ?? 'mp') ?? '',
            'sp_mchid' => $config->getMchId(),
            'sub_mchid' => $payload->get('sub_mchid', $config->getSubMchId() ?? ''),
        ];
    }
}
