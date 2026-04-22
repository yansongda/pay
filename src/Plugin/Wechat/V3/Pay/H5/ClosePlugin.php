<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\Pay\H5;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Direction\OriginResponseDirection;
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
 * @see https://pay.weixin.qq.com/docs/merchant/apis/h5-payment/close-order.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/partner-h5-payment/close-order.html
 */
class ClosePlugin implements PluginInterface
{
    use WechatTrait;

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][V3][Pay][H5][ClosePlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();

        /** @var WechatConfig $config */
        $config = self::getProviderConfig('wechat', $params);
        $payload = $rocket->getPayload();
        $outTradeNo = $payload?->get('out_trade_no') ?? null;

        if (empty($outTradeNo)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: H5 关闭订单，参数缺少 `out_trade_no`');
        }

        if (Pay::MODE_SERVICE === $config->getMode()) {
            $data = $this->service($payload, $config);
        }

        $rocket->setDirection(OriginResponseDirection::class)
            ->setPayload(array_merge(
                [
                    '_method' => 'POST',
                    '_url' => 'v3/pay/transactions/out-trade-no/'.$outTradeNo.'/close',
                    '_service_url' => 'v3/pay/partner/transactions/out-trade-no/'.$outTradeNo.'/close',
                ],
                $data ?? $this->normal($config)
            ));

        Logger::info('[Wechat][V3][Pay][H5][ClosePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function normal(WechatConfig $config): array
    {
        return [
            'mchid' => $config->getMchId(),
        ];
    }

    protected function service(Collection $payload, WechatConfig $config): array
    {
        return [
            'sp_mchid' => $config->getMchId(),
            'sub_mchid' => $payload->get('sub_mchid', $config->getSubMchId() ?? ''),
        ];
    }
}
