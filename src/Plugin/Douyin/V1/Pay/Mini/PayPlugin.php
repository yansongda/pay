<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Douyin\V1\Pay\Mini;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Supports\Collection;

use function Yansongda\Pay\get_provider_config;

/**
 * @see https://developer.open-douyin.com/docs/resource/zh-CN/mini-app/develop/server/ecpay/pay-list/pay
 */
class PayPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Douyin][V1][Pay][Mini][PayPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();
        $params = $rocket->getParams();
        $config = get_provider_config('douyin', $params);

        if (is_null($payload)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 抖音小程序下单，参数为空');
        }

        if (Pay::MODE_SERVICE === ($config['mode'] ?? Pay::MODE_NORMAL)) {
            $data = $this->service($payload, $config);
        }

        $rocket->mergePayload(array_merge(
            [
                '_method' => 'POST',
                '_url' => 'api/apps/ecpay/v1/create_order',
                'app_id' => $config['mini_app_id'] ?? '',
                'notify_url' => $payload->get('notify_url') ?? $this->getNotifyUrl($config),
            ],
            $data ?? [],
        ));

        Logger::info('[Douyin][V1][Pay][Mini][PayPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function service(Collection $payload, array $config): array
    {
        return [
            'thirdparty_id' => $payload->get('thirdparty_id', $config['thirdparty_id'] ?? ''),
        ];
    }

    protected function getNotifyUrl(array $config): ?string
    {
        return empty($config['notify_url']) ? null : $config['notify_url'];
    }
}
