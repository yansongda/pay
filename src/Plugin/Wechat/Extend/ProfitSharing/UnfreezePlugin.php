<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Extend\ProfitSharing;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Rocket;

use function Yansongda\Pay\get_wechat_config;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/profit-sharing/orders/unfreeze-order.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/profit-sharing/orders/unfreeze-order.html
 */
class UnfreezePlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Extend][ProfitSharing][UnfreezePlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = get_wechat_config($params);
        $payload = $rocket->getPayload();

        if (is_null($payload)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 缺少分账参数');
        }

        if (Pay::MODE_SERVICE === ($config['mode'] ?? Pay::MODE_NORMAL)) {
            $data = $this->service($config);
        }

        $rocket->mergePayload(array_merge(
            [
                '_method' => 'POST',
                '_url' => 'v3/profitsharing/orders/unfreeze',
                '_service_url' => 'v3/profitsharing/orders/unfreeze',
            ],
            $data ?? $this->normal(),
        ));

        Logger::info('[Wechat][Extend][ProfitSharing][UnfreezePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function normal(): array
    {
        return [];
    }

    protected function service(array $config): array
    {
        return [
            'sub_mchid' => $config['sub_mch_id'],
            'notify_url' => $config['notify_url'] ?? null,
        ];
    }
}
