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
use Yansongda\Pay\Config\JsbConfig;
use Yansongda\Pay\Traits\JsbTrait;
use Yansongda\Supports\Str;

class StartPlugin implements PluginInterface
{
    use JsbTrait;

    /**
     * @throws ServiceNotFoundException
     * @throws ContainerException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::info('[Jsb][StartPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();

        /** @var JsbConfig $config */
        $config = self::getProviderConfig('jsb', $params);

        $rocket->setPacker(QueryPacker::class)
            ->mergePayload(array_merge($params, [
                'createData' => date('Ymd'),
                'createTime' => date('His'),
                'bizDate' => date('Ymd'),
                'msgId' => Str::uuidV4(),
                'svrCode' => $config->getSvrCode() ?? '',
                'partnerId' => $config->getPartnerId() ?? '',
                'channelNo' => 'm',
                'publicKeyCode' => $config->getPublicKeyCode() ?? '',
                'version' => 'v1.0.0',
                'charset' => 'utf-8',
            ]));

        Logger::info('[Jsb][StartPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
