<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Epay;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Supports\Str;

use function Yansongda\Pay\get_provider_config;

class StartPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     * @throws InvalidConfigException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::info('[epay][StartPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = get_provider_config('epay', $params);

        $rocket->mergePayload(array_merge(
            $params,
            [
                'createData' => date('Ymd'),
                'createTime' => date('His'),
                'bizDate' => date('Ymd'),
                'msgId' => Str::uuidV4(),
                'svrCode' => $config['svr_code'] ?? '',
                'partnerId' => $config['partner_id'] ?? '',
                'channelNo' => 'm',
                'publicKeyCode' => $config['public_key_code'] ?? '',
                'version' => 'v1.0.0',
                'charset' => 'utf-8',
            ]
        ));
        Logger::info('[epay][StartPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
