<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Jsb;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Packer\QueryPacker;
use Yansongda\Artful\Rocket;
use Yansongda\Supports\Str;

use function Yansongda\Pay\get_provider_config;

class StartPlugin implements PluginInterface
{
    /**
     * @throws ServiceNotFoundException
     * @throws ContainerException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::info('[Jsb][StartPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = get_provider_config('jsb', $params);

        $rocket->setPacker(QueryPacker::class)
            ->mergePayload(array_merge($params, [
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
            ]));

        Logger::info('[Jsb][StartPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
