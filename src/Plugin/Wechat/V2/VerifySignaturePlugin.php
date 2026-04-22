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
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Traits\WechatTrait;

use function Yansongda\Artful\should_do_http_request;

class VerifySignaturePlugin implements PluginInterface
{
    use WechatTrait;

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws ServiceNotFoundException
     * @throws InvalidSignException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        /* @var Rocket $rocket */
        $rocket = $next($rocket);

        Logger::debug('[Wechat][V2][VerifySignaturePlugin] 插件开始装载', ['rocket' => $rocket]);

        /** @var WechatConfig $config */
        $config = self::getProviderConfig('wechat', $rocket->getParams());

        if (!should_do_http_request($rocket->getDirection())) {
            return $rocket;
        }

        self::verifyWechatSignV2($config, $rocket->getDestination()?->all() ?? []);

        Logger::info('[Wechat][V2][VerifySignaturePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }
}
