<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\PayScore;

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
 * @see https://pay.weixin.qq.com/doc/v3/merchant/4012587905
 * @see https://pay.weixin.qq.com/doc/v3/merchant/4012647422
 */
class CancelPlugin implements PluginInterface
{
    use WechatTrait;

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][V3][PayScore][CancelPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();
        $params = $rocket->getParams();

        if (is_null($payload) || empty($outOrderNo = $payload->get('out_order_no'))) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 取消支付分订单，参数缺少 `out_order_no`');
        }

        /** @var WechatConfig $config */
        $config = self::getProviderConfig(Pay::PROVIDER_WECHAT, $params);

        if (Pay::MODE_SERVICE === $config->getMode()) {
            throw new InvalidParamsException(Exception::PARAMS_PLUGIN_ONLY_SUPPORT_NORMAL_MODE, '参数异常: 取消支付分订单，只支持普通商户模式，当前配置为服务商模式');
        }

        $serviceId = $payload->get('service_id') ?? $config->getServiceId();

        if (empty($serviceId)) {
            throw new InvalidParamsException(Exception::PARAMS_WECHAT_SERVICE_ID_MISSING, '参数异常: 取消支付分订单，参数缺少 `service_id`');
        }

        $appid = $payload->get('appid') ?? $config->getAppIdByType($params['_type'] ?? 'mp');

        if (empty($appid)) {
            throw new InvalidParamsException(Exception::PARAMS_WECHAT_APPID_MISSING, '参数异常: 缺少公众账号ID -- [appid]');
        }

        $rocket->mergePayload([
            '_method' => 'POST',
            '_url' => '/v3/payscore/serviceorder/'.$outOrderNo.'/cancel',
            'appid' => $appid,
            'service_id' => $serviceId,
        ]);

        $rocket->getPayload()?->forget('out_order_no');

        Logger::info('[Wechat][V3][PayScore][CancelPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
