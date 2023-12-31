<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Request;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;

use function Yansongda\Pay\get_wechat_body;
use function Yansongda\Pay\get_wechat_config;
use function Yansongda\Pay\get_wechat_method;
use function Yansongda\Pay\get_wechat_url;

class AddRadarPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][AddRadarPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $payload = $rocket->getPayload();
        $config = get_wechat_config($params);

        $rocket->setRadar(new Request(
            get_wechat_method($payload),
            get_wechat_url($config, $payload),
            $this->getHeaders($payload),
            get_wechat_body($payload),
        ));

        Logger::info('[Wechat][AddRadarPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    /**
     * @throws InvalidParamsException
     */
    protected function getHeaders(?Collection $payload): array
    {
        $authorization = $payload?->get('_authorization') ?? null;

        if (empty($authorization)) {
            throw new InvalidParamsException(Exception::PARAMS_WECHAT_AUTHORIZATION_MISSING, '参数异常: 微信 `_authorization` 参数缺失：你可能用错插件顺序，应该先使用 `AddPayloadSignaturePlugin`, 再使用 `AddRadarPlugin`');
        }

        $headers = [
            'Accept' => 'application/json, text/plain, application/x-gzip',
            'User-Agent' => 'yansongda/pay-v3',
            'Content-Type' => 'application/json; charset=utf-8',
            'Authorization' => $authorization,
        ];

        // 当 body 里有加密内容时，需要传递此参数用于微信区分
        if ($payload->has('_serial_no')) {
            $headers['Wechatpay-Serial'] = $payload->get('_serial_no');
        }

        if ($payload->has('_content-type')) {
            $headers['Content-Type'] = $payload->get('_content-type');
        }

        if ($payload->has('_accept')) {
            $headers['Accept'] = $payload->get('_accept');
        }

        return $headers;
    }
}
