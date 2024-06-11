<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V2;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\Exception;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Supports\Collection;

use function Yansongda\Artful\should_do_http_request;
use function Yansongda\Pay\get_alipay_error_response_message;

class ResponsePlugin implements PluginInterface
{
    /**
     * @throws InvalidResponseException
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        /* @var Rocket $rocket */
        $rocket = $next($rocket);

        Logger::debug('[Alipay][ResponsePlugin] 插件开始装载', ['rocket' => $rocket]);

        $destination = $rocket->getDestination();
        $payload = $rocket->getPayload();
        $resultKey = str_replace('.', '_', $payload->get('method')).'_response';

        if (should_do_http_request($rocket->getDirection()) && $destination instanceof Collection) {
            $sign = $destination->get('sign', '');
            $response = $destination->get($resultKey, $destination->all());

            // 支付宝返回sign为空时候，非应用异常，属于网关错误。
            // 例如AppID配置错误
            if (empty($sign)) {
                if (empty($response['code'])) {
                    throw new InvalidParamsException(Exception::RESPONSE_EMPTY, '参数异常: 支付宝响应内容异常; 缺失code', $destination);
                }

                if ('10000' !== $response['code']) {
                    throw new InvalidResponseException(Exception::RESPONSE_ERROR, sprintf('支付宝网关响应异常; %s', get_alipay_error_response_message($response)), $rocket->getDestination());
                }
            }

            $rocket->setDestination(new Collection(array_merge(
                ['_sign' => $sign],
                $response
            )));
        }

        Logger::info('[Alipay][ResponsePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }
}
