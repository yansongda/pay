<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Rocket;

class PreparePlugin implements PluginInterface
{
    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        $rocket->mergePayload($this->getPayload($rocket->getParams()));

        return $next($rocket);
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    protected function getPayload(array $params): array
    {
        return [
            'app_id' => get_alipay_config($params)->get('app_id', ''),
            'method' => '',
            'format' => 'JSON',
            'return_url' => get_alipay_config($params)->get('return_url', ''),
            'charset' => 'utf-8',
            'sign_type' => 'RSA2',
            'sign' => '',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0',
            'notify_url' => get_alipay_config($params)->get('notify_url', ''),
            'app_auth_token' => '',
            'biz_content' => [],
        ];
    }
}
