<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Extend\Complaints;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/consumer-complaint/complaints/query-complaint-v2.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/consumer-complaint/complaints/query-complaint-v2.html
 */
class QueryDetailPlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Extend][Complaints][QueryDetailPlugin] 插件开始装载', ['rocket' => $rocket]);

        $complaintId = $rocket->getPayload()?->get('complaint_id') ?? null;

        if (empty($complaintId)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 查询投诉单详情，参数缺少 `complaint_id`');
        }

        $rocket->setPayload([
            '_method' => 'GET',
            '_url' => 'v3/merchant-service/complaints-v2/'.$complaintId,
            '_service_url' => 'v3/merchant-service/complaints-v2/'.$complaintId,
        ]);

        Logger::info('[Wechat][Extend][Complaints][QueryDetailPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
