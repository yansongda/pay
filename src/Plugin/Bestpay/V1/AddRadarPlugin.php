<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Bestpay\V1;

use Closure;
use GuzzleHttp\Psr7\Request;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\BestpayConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Traits\BestpayTrait;
use Yansongda\Supports\Collection;

class AddRadarPlugin implements PluginInterface
{
    use BestpayTrait;

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Bestpay][V1][AddRadarPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $payload = $rocket->getPayload();

        /** @var BestpayConfig $config */
        $config = self::getProviderConfig(Pay::PROVIDER_BESTPAY, $params);

        if (empty($payload) || $payload->isEmpty()) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 缺少支付必要参数，可能插件用错顺序');
        }

        $url = self::getBestpayUrl($config, $payload)
            .'?BESTPAY_MAPI_VERSION='.rawurlencode($config->getApiVersion());

        $rocket->setRadar(new Request(
            strtoupper($params['_method'] ?? 'POST'),
            $url,
            $this->getHeaders(),
            $this->getBody($payload),
        ));

        Logger::info('[Bestpay][V1][AddRadarPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    /**
     * @return array<string, string>
     */
    protected function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/x-www-form-urlencoded;charset=utf-8',
            'User-Agent' => 'yansongda/pay-v3',
            'Accept' => 'application/json',
        ];
    }

    protected function getBody(Collection $payload): string
    {
        return http_build_query($payload->all());
    }
}
