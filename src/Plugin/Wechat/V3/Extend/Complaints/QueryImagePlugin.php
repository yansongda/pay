<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\Extend\Complaints;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/consumer-complaint/images/query-images.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/consumer-complaint/images/query-images.html
 */
class QueryImagePlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Extend][Complaints][QueryImagePlugin] 插件开始装载', ['rocket' => $rocket]);

        $mediaId = $rocket->getPayload()?->get('media_id') ?? null;

        if (empty($mediaId)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 图片请求接口，参数缺少 `media_id`');
        }

        $rocket->setPayload([
            '_method' => 'GET',
            '_url' => 'v3/merchant-service/images/'.$mediaId,
            '_service_url' => 'v3/merchant-service/images/'.$mediaId,
        ]);

        Logger::info('[Wechat][Extend][Complaints][QueryImagePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
