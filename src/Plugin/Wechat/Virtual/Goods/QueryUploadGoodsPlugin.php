<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Virtual\Goods;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

/**
 * @see https://developers.weixin.qq.com/miniprogram/dev/server/API/VirtualPayment/api_query_upload_goods
 */
class QueryUploadGoodsPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Virtual][Goods][QueryUploadGoodsPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            '_method' => 'POST',
            '_url' => 'xpay/query_upload_goods',
        ]);

        Logger::info('[Wechat][Virtual][Goods][QueryUploadGoodsPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
