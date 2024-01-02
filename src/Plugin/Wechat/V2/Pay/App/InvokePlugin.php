<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V2\Pay\App;

use Closure;
use Throwable;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\InvalidResponseException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Config;
use Yansongda\Supports\Str;

use function Yansongda\Pay\get_wechat_config;
use function Yansongda\Pay\get_wechat_sign_v2;

/**
 * @see https://pay.weixin.qq.com/wiki/doc/api/app/app.php?chapter=8_5
 */
class InvokePlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws Exception
     * @throws InvalidResponseException
     * @throws ServiceNotFoundException
     * @throws Throwable                生成随机串失败
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        /* @var Rocket $rocket */
        $rocket = $next($rocket);

        Logger::debug('[Wechat][V2][Pay][App][InvokePlugin] 插件开始装载', ['rocket' => $rocket]);

        $destination = $rocket->getDestination();
        $prepayId = $destination?->get('prepay_id') ?? null;

        if (is_null($prepayId)) {
            Logger::error('[Wechat][V2][Pay][App][InvokePlugin] 预下单失败：响应缺少 prepay_id 参数，请自行检查参数是否符合微信要求', $destination?->all() ?? null);

            throw new InvalidResponseException(Exception::RESPONSE_MISSING_NECESSARY_PARAMS, $destination?->get('message') ?? '预下单失败：响应缺少 prepay_id 参数，请自行检查参数是否符合微信要求', $destination->all() ?? null);
        }

        $params = $rocket->getParams();
        $config = get_wechat_config($params);
        $payload = $rocket->getPayload();

        $rocket->setDestination($this->getInvokeConfig($payload, $config, $prepayId));

        Logger::info('[Wechat][V2][Pay][App][InvokePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }

    /**
     * @throws InvalidConfigException
     * @throws Throwable              生成随机串失败
     */
    protected function getInvokeConfig(?Collection $payload, array $config, string $prepayId): Config
    {
        $invokeConfig = new Config([
            'appId' => $this->getAppId($payload, $config),
            'partnerId' => $this->getPartnerId($payload, $config),
            'prepayId' => $prepayId,
            'package' => 'Sign=WXPay',
            'nonceStr' => Str::random(32),
            'timeStamp' => time().'',
        ]);

        $invokeConfig->set('sign', get_wechat_sign_v2($config, $invokeConfig->all()));

        return $invokeConfig;
    }

    protected function getAppId(?Collection $payload, array $config): string
    {
        if (Pay::MODE_SERVICE === ($config['mode'] ?? Pay::MODE_NORMAL)) {
            return $payload?->get('_invoke_appid') ?? $config['sub_app_id'] ?? '';
        }

        return $payload?->get('_invoke_appid') ?? $config['app_id'] ?? '';
    }

    protected function getPartnerId(?Collection $payload, array $config): string
    {
        return $payload?->get('_invoke_partnerid') ?? $config['mch_id'] ?? '';
    }
}
