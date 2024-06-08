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

use function Yansongda\Pay\get_douyin_config;

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
        Logger::debug('[Douyin][Pay][Mini][PayPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();
        $params = $rocket->getParams();
        $config = get_douyin_config($params);

        if (is_null($payload)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: Mini 下单，参数为空');
        }

        if (Pay::MODE_SERVICE === ($config['mode'] ?? Pay::MODE_NORMAL)) {
            $data = $this->service($payload, $config);
        }

        $rocket->mergePayload(array_merge(
            [
                '_method' => 'POST',
                '_url' => 'api/apps/ecpay/v1/create_order',
                'notify_url' => $payload->get('notify_url', $config['notify_url'] ?? null),
            ],
            $data ?? $this->normal($config)
        ));

        Logger::info('[Douyin][Pay][Mini][PayPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function normal(array $config): array
    {
        return [
            'appid' => $config['mini_app_id'] ?? '',
        ];
    }

    protected function service(Collection $payload, array $config): array
    {
        $data = [
            'sp_appid' => $config['mini_app_id'] ?? '',
            'sp_mchid' => $config['mch_id'] ?? '',
            'sub_mchid' => $payload->get('sub_mchid', $config['sub_mch_id'] ?? ''),
        ];

        if ($payload->has('payer.sub_openid')) {
            $data['sub_appid'] = $config['sub_mini_app_id'] ?? '';
        }

        return $data;
    }
}
