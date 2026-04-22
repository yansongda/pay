<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V2;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\WechatConfig;
use Yansongda\Pay\Traits\WechatTrait;

use function Yansongda\Artful\filter_params;

class AddPayloadSignaturePlugin implements PluginInterface
{
    use WechatTrait;

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][V2][AddPayloadSignaturePlugin] 插件开始装载', ['rocket' => $rocket]);

        /** @var WechatConfig $config */
        $config = self::getProviderConfig('wechat', $rocket->getParams());

        $rocket->mergePayload([
            'sign' => self::getWechatSignV2($config, filter_params($rocket->getPayload())->all()),
        ]);

        Logger::info('[Wechat][V2][AddPayloadSignaturePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
