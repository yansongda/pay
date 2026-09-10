<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Allinpay;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\AllinpayConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Traits\AllinpayTrait;
use Yansongda\Supports\Collection;

/**
 * @see https://prodoc.allinpay.com/doc/2318/ 通联支付接口安全规范（RSA-SHA1 签名）
 */
class AddPayloadSignPlugin implements PluginInterface
{
    use AllinpayTrait;

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::info('[Allinpay][AddPayloadSignPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();

        /** @var AllinpayConfig $config */
        $config = self::getProviderConfig(Pay::PROVIDER_ALLINPAY, $params);
        $payload = $rocket->getPayload();

        if (empty($payload) || $payload->isEmpty()) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 缺少支付必要参数。可能插件用错顺序，应该先使用 `业务插件`');
        }

        if (empty($config->getMchSecretKey())) {
            throw new InvalidConfigException(Exception::CONFIG_ALLINPAY_INVALID, '配置异常: 缺少配置参数 -- [mch_secret_key]');
        }

        self::normalizePayloadArrayParams($payload);

        $rocket->mergePayload([
            'sign' => self::getAllinpaySign($config, $payload),
        ]);

        Logger::info('[Allinpay][AddPayloadSignPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    /**
     * 通联官方契约中复合参数（terminfo/benefitdetail/extendparams 等）均为 json 字符串（"注意是 String"）.
     *
     * 为避免调用方直接传数组时，签名串跳过该参数而请求体却以 `key[子键]=` 展开导致通联侧拒签，
     * 统一在签名前将数组参数转为 json 字符串，保证签名与请求体一致。
     */
    protected static function normalizePayloadArrayParams(Collection $payload): void
    {
        foreach ($payload->toArray() as $key => $value) {
            if (is_array($value)) {
                $payload->set($key, json_encode($value, JSON_UNESCAPED_UNICODE));
            }
        }
    }
}
