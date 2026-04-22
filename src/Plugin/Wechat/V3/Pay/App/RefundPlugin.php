<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\Pay\App;

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
 * @see https://pay.weixin.qq.com/docs/merchant/apis/in-app-payment/create.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/partner-in-app-payment/create.html
 */
class RefundPlugin implements PluginInterface
{
    use WechatTrait;

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][V3][Pay][App][RefundPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = self::getProviderConfig('wechat', $params);
        $payload = $rocket->getPayload();

        if (is_null($payload)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: App 退款申请，参数为空');
        }

        if (Pay::MODE_SERVICE === $config->getMode()) {
            $data = $this->service($payload, $config);
        }

        $rocket->mergePayload(array_merge(
            [
                '_method' => 'POST',
                '_url' => 'v3/refund/domestic/refunds',
                '_service_url' => 'v3/refund/domestic/refunds',
                'notify_url' => $payload->get('notify_url', $config->getNotifyUrl()),
            ],
            $data ?? $this->normal()
        ));

        Logger::info('[Wechat][V3][Pay][App][RefundPlugin] 插件装载完毕', ['rocket' => $rocket]);

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
        ];
    }
}
