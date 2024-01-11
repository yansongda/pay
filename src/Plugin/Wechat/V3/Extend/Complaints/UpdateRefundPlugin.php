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
 * @see https://pay.weixin.qq.com/docs/merchant/apis/consumer-complaint/complaints/update-refund-progress.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/consumer-complaint/complaints/update-refund-progress.html
 */
class UpdateRefundPlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Extend][Complaints][UpdateRefundPlugin] 插件开始装载', ['rocket' => $rocket]);

        $complaintId = $rocket->getPayload()?->get('complaint_id') ?? null;

        if (empty($complaintId)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 更新退款审批结果，参数缺少 `complaint_id`');
        }

        $rocket->mergePayload([
            '_method' => 'POST',
            '_url' => 'v3/merchant-service/complaints-v2/'.$complaintId.'/update-refund-progress',
            '_service_url' => 'v3/merchant-service/complaints-v2/'.$complaintId.'/update-refund-progress',
        ])->exceptPayload('complaint_id');

        Logger::info('[Wechat][Extend][Complaints][UpdateRefundPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
