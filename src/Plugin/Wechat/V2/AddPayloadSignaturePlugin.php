<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V2;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

use function Yansongda\Pay\filter_params;
use function Yansongda\Pay\get_wechat_config;
use function Yansongda\Pay\get_wechat_sign_v2;

class AddPayloadSignaturePlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][V2][AddPayloadSignaturePlugin] 插件开始装载', ['rocket' => $rocket]);

        $config = get_wechat_config($rocket->getParams());

        $rocket->mergePayload([
            'sign' => get_wechat_sign_v2($config, filter_params($rocket->getPayload()->all())),
        ]);

        Logger::info('[Wechat][V2][AddPayloadSignaturePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
