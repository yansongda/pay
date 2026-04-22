<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\Pay\Mini;

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
 * @see https://pay.weixin.qq.com/docs/merchant/apis/mini-program-payment/mini-prepay.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/partner-mini-program-payment/partner-mini-prepay.html
 */
class PayPlugin implements PluginInterface
{
    use WechatTrait;

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Pay][Mini][PayPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();
        $params = $rocket->getParams();

        /** @var WechatConfig $config */
        $config = self::getProviderConfig('wechat', $params);

        if (is_null($payload)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: Mini 下单，参数为空');
        }

        if (Pay::MODE_SERVICE === $config->getMode()) {
            $data = $this->service($payload, $config);
        }

        $rocket->mergePayload(array_merge(
            [
                '_method' => 'POST',
                '_url' => 'v3/pay/transactions/jsapi',
                '_service_url' => 'v3/pay/partner/transactions/jsapi',
                'notify_url' => $payload->get('notify_url', $config->getNotifyUrl()),
            ],
            $data ?? $this->normal($config)
        ));

        Logger::info('[Wechat][Pay][Mini][PayPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function normal(WechatConfig $config): array
    {
        return [
            'appid' => $config->getMiniAppId() ?? '',
            'mchid' => $config->getMchId(),
        ];
    }

    protected function service(Collection $payload, WechatConfig $config): array
    {
        $data = [
            'sp_appid' => $config->getMiniAppId() ?? '',
            'sp_mchid' => $config->getMchId(),
            'sub_mchid' => $payload->get('sub_mchid', $config->getSubMchId() ?? ''),
        ];

        if ($payload->has('payer.sub_openid')) {
            $data['sub_appid'] = $config->getSubMiniAppId() ?? '';
        }

        return $data;
    }
}
