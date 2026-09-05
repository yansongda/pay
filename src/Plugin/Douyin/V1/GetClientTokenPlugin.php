<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Douyin\V1;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\DouyinConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Traits\ProviderConfigTrait;

/**
 * @see https://developer.open-douyin.com/docs/resource/zh-CN/mini-app/develop/server/basic-abilities/interface-request-credential/non-user-authorization/get-client_token
 */
class GetClientTokenPlugin implements PluginInterface
{
    use ProviderConfigTrait;

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Douyin][V1][GetClientTokenPlugin] 插件开始装载', ['rocket' => $rocket]);

        /** @var DouyinConfig $config */
        $config = self::getProviderConfig(Pay::PROVIDER_DOUYIN, $rocket->getParams());

        if (empty($config->getAppId()) || empty($config->getAppSecret())) {
            throw new InvalidConfigException(Exception::CONFIG_DOUYIN_INVALID, '配置异常: 缺少抖音配置 -- [app_id] 或 [app_secret]');
        }

        $rocket->mergePayload([
            '_method' => 'POST',
            '_url' => '/oauth/client_token/',
            'grant_type' => 'client_credential',
            'client_key' => $config->getAppId(),
            'client_secret' => $config->getAppSecret(),
        ]);

        Logger::info('[Douyin][V1][GetClientTokenPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
