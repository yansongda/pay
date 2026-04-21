<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\Extend\Complaints;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\WechatConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Traits\WechatTrait;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/consumer-complaint/complaints/response-complaint-v2.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/consumer-complaint/complaints/response-complaint-v2.html
 */
class ResponsePlugin implements PluginInterface
{
    use WechatTrait;

    /**
     * @throws InvalidParamsException
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Extend][Complaints][ResponsePlugin] 插件开始装载', ['rocket' => $rocket]);

        /** @var WechatConfig $config */
        $config = self::getProviderConfig('wechat', $rocket->getParams());
        $payload = $rocket->getPayload();
        $complaintId = $payload?->get('complaint_id') ?? null;

        if (empty($complaintId)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 回复用户，参数缺少 `complaint_id`');
        }

        $rocket->mergePayload([
            '_method' => 'POST',
            '_url' => 'v3/merchant-service/complaints-v2/'.$complaintId.'/response',
            '_service_url' => 'v3/merchant-service/complaints-v2/'.$complaintId.'/response',
            'complainted_mchid' => $payload->get('complainted_mchid', $config->getMchId()),
        ])->exceptPayload('complaint_id');

        Logger::info('[Wechat][Extend][Complaints][ResponsePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
