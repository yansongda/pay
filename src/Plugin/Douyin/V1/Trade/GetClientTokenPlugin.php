<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Douyin\V1\Trade;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\Douyin;

use function Yansongda\Pay\get_provider_config;

/**
 * @see https://developer.open-douyin.com/docs/resource/zh-CN/mini-app/develop/server/basic-abilities/interface-request-credential/non-user-authorization/get-client_token
 */
class GetClientTokenPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Douyin][V1][Trade][GetClientTokenPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = get_provider_config('douyin', $params);

        if (empty($config['app_id']) || empty($config['app_secret'])) {
            throw new InvalidConfigException(Exception::CONFIG_DOUYIN_INVALID, '配置异常: 缺少抖音交易系统配置 -- [app_id] or [app_secret]');
        }

        $rocket->mergePayload([
            '_method' => 'POST',
            '_url' => Douyin::TRADE_URL[$config['mode'] ?? Pay::MODE_NORMAL].'oauth/client_token/',
            'client_key' => $config['app_id'],
            'client_secret' => $config['app_secret'],
            'grant_type' => 'client_credential',
        ]);

        Logger::info('[Douyin][V1][Trade][GetClientTokenPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
