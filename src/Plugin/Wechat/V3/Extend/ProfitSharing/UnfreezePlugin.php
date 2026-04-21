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
 * @see https://pay.weixin.qq.com/docs/merchant/apis/profit-sharing/orders/unfreeze-order.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/profit-sharing/orders/unfreeze-order.html
 */
class UnfreezePlugin implements PluginInterface
{
    use WechatTrait;

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Extend][ProfitSharing][UnfreezePlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = self::getProviderConfig('wechat', $params);
        $payload = $rocket->getPayload();

        if (is_null($payload)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 缺少分账解冻剩余资金参数');
        }

        if (Pay::MODE_SERVICE === $config->getMode()) {
            $data = $this->service($payload, $config);
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

    protected function service(Collection $payload, WechatConfig $config): array
    {
        return [
            'sub_mchid' => $payload->get('sub_mchid', $config->getSubMchId() ?? ''),
            'notify_url' => $payload->get('notify_url') ?? $config->getNotifyUrl(),
        ];
    }
}
