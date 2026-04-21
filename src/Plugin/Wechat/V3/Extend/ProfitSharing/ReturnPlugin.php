<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\Extend\ProfitSharing;

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
use Yansongda\Supports\Collection;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/profit-sharing/return-orders/create-return-order.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/profit-sharing/return-orders/create-return-order.html
 */
class ReturnPlugin implements PluginInterface
{
    use WechatTrait;

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Extend][ProfitSharing][ReturnPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = self::getProviderConfig('wechat', $params);
        $payload = $rocket->getPayload();

        if (is_null($payload)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 缺少分账退回参数');
        }

        if (Pay::MODE_SERVICE === $config->getMode()) {
            $data = $this->service($payload, $config);
        }

        $rocket->mergePayload(array_merge(
            [
                '_method' => 'POST',
                '_url' => 'v3/profitsharing/return-orders',
                '_service_url' => 'v3/profitsharing/return-orders',
            ],
            $data ?? $this->normal($payload, $config),
        ));

        Logger::info('[Wechat][Extend][ProfitSharing][ReturnPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function normal(Collection $payload, WechatConfig $config): array
    {
        return [
            'return_mchid' => $payload->get('return_mchid', $config->getMchId()),
        ];
    }

    protected function service(Collection $payload, WechatConfig $config): array
    {
        return [
            'sub_mchid' => $payload->get('sub_mchid', $config->getSubMchId() ?? ''),
            'return_mchid' => $payload->get('return_mchid', $config->getMchId()),
        ];
    }
}
