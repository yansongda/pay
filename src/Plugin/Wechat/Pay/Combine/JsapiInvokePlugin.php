<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\Combine;

use Closure;
use Throwable;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\InvalidResponseException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Config;
use Yansongda\Supports\Str;

use function Yansongda\Pay\get_wechat_config;
use function Yansongda\Pay\get_wechat_config_type_key;
use function Yansongda\Pay\get_wechat_sign;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/combine-payment/orders/jsapi-transfer-payment.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/combine-payment/orders/jsapi-transfer-payment.html
 */
class JsapiInvokePlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidResponseException
     * @throws ServiceNotFoundException
     * @throws Throwable                生成随机串失败
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        /* @var Rocket $rocket */
        $rocket = $next($rocket);

        Logger::debug('[Wechat][Pay][Combine][JsapiInvokePlugin] 插件开始装载', ['rocket' => $rocket]);

        $destination = $rocket->getDestination();

        $prepayId = $destination->get('prepay_id');

        if (is_null($prepayId)) {
            Logger::error('[Wechat][Pay][Combine][JsapiInvokePlugin] 预下单失败：响应缺少 `prepay_id` 参数，请自行检查参数是否符合微信要求', $destination->all());

            throw new InvalidResponseException(Exception::RESPONSE_MISSING_NECESSARY_PARAMS, $destination->get('message', '预下单失败：响应缺少 `prepay_id` 参数，请自行检查参数是否符合微信要求'), $destination->all());
        }

        $params = $rocket->getParams();
        $config = get_wechat_config($params);

        $rocket->setDestination($this->getInvokeConfig($params, $config, $prepayId));

        Logger::info('[Wechat][Pay][Combine][JsapiInvokePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }

    /**
     * @throws InvalidConfigException
     * @throws Throwable              生成随机串失败
     */
    protected function getInvokeConfig(array $params, array $config, string $prepayId): Config
    {
        $invokeConfig = new Config([
            'appId' => $config[get_wechat_config_type_key($params)] ?? '',
            'timeStamp' => time().'',
            'nonceStr' => Str::random(32),
            'package' => 'prepay_id='.$prepayId,
            'signType' => 'RSA',
        ]);

        $invokeConfig->set('paySign', $this->getSign($invokeConfig, $config));

        return $invokeConfig;
    }

    /**
     * @throws InvalidConfigException
     */
    protected function getSign(Collection $invokeConfig, array $config): string
    {
        $contents = $invokeConfig->get('appId', '')."\n".
            $invokeConfig->get('timeStamp', '')."\n".
            $invokeConfig->get('nonceStr', '')."\n".
            $invokeConfig->get('package', '')."\n";

        return get_wechat_sign($config, $contents);
    }
}
