<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\InvalidResponseException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

use function Yansongda\Pay\should_do_http_request;
use function Yansongda\Pay\verify_alipay_sign;

class VerifySignaturePlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidResponseException
     * @throws ServiceNotFoundException
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

        $sign = $destination->get('_sign', '');
        $result = $destination->except('_sign')->all();

        if ('' === $sign || empty($result)) {
            throw new InvalidResponseException(Exception::SIGN_INVALID, 'Verify Alipay Response Sign Failed: sign is empty', $destination);
        }

        verify_alipay_sign($rocket->getParams(), json_encode($result, JSON_UNESCAPED_UNICODE), $sign);

        Logger::info('[Alipay][VerifySignaturePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }
}
