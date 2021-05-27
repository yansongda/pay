<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay;

use Closure;
use Yansongda\Supports\Collection;

class IgnitePlugin
{
    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    public function apply(array $params, Collection $payload, Closure $next): Collection
    {
        $payload->merge([
            'app_id' => get_alipay_config($params)['app_id'] ?? '',
            'method' => '',
            'format' => 'JSON',
            'return_url' => get_alipay_config($params)['return_url'] ?? '',
            'charset' => 'utf-8',
            'sign_type' => 'RSA2',
            'sign' => '',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0',
            'notify_url' => get_alipay_config($params)['notify_url'] ?? '',
            'app_auth_token' => get_alipay_config($params)['app_auth_token'] ?? '',
            'biz_content' => '',
        ]);

        return $next($params, $payload);
    }
}
