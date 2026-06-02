<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Virtual\Subscribe;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Traits\WechatTrait;

/**
 * @see https://developers.weixin.qq.com/miniprogram/dev/platform-capabilities/business-capabilities/vip.html
 */
class QuerySubscribeContractPlugin implements PluginInterface
{
    use WechatTrait;

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Virtual][Subscribe][QuerySubscribeContractPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();


        $rocket->mergePayload([
            '_method' => 'POST',
            '_url' => '/xpay/query_subscribe_contract',
            'openid' => $payload->get('openid'),
        ]);

        Logger::info('[Wechat][Virtual][Subscribe][QuerySubscribeContractPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
