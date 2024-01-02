<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V2;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;

use function Yansongda\Pay\get_alipay_config;
use function Yansongda\Pay\should_do_http_request;
use function Yansongda\Pay\verify_alipay_sign;

class VerifySignaturePlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws ServiceNotFoundException
     * @throws InvalidSignException
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        /* @var Rocket $rocket */
        $rocket = $next($rocket);

        Logger::debug('[Alipay][VerifySignaturePlugin] 插件开始装载', ['rocket' => $rocket]);

        if (!should_do_http_request($rocket->getDirection())) {
            return $rocket;
        }

        $destination = $rocket->getDestination();

        if ((!$destination instanceof Collection) || empty($result = $destination->except('_sign')->all())) {
            throw new InvalidParamsException(Exception::RESPONSE_EMPTY, '参数异常: 支付宝验证签名时待验签参数不正确', $destination);
        }

        $config = get_alipay_config($rocket->getParams());

        verify_alipay_sign($config, json_encode($result, JSON_UNESCAPED_UNICODE), $destination->get('_sign', ''));

        Logger::info('[Alipay][VerifySignaturePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }
}
