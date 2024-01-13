<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\Extend\Complaints;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Supports\Collection;

use function Yansongda\Artful\filter_params;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/consumer-complaint/complaints/query-negotiation-history-v2.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/consumer-complaint/complaints/query-negotiation-history-v2.html
 */
class QueryNegotiationPlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Extend][Complaints][QueryNegotiationPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();
        $complaintId = $payload?->get('complaint_id') ?? null;

        if (empty($complaintId)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 查询投诉单协商历史，参数缺少 `complaint_id`');
        }

        $query = $this->normal($payload);

        $rocket->setPayload([
            '_method' => 'GET',
            '_url' => 'v3/merchant-service/complaints-v2/'.$complaintId.'/negotiation-historys'.$query,
            '_service_url' => 'v3/merchant-service/complaints-v2/'.$complaintId.'/negotiation-historys'.$query,
        ]);

        Logger::info('[Wechat][Extend][Complaints][QueryNegotiationPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function normal(Collection $payload): string
    {
        $query = filter_params($payload)->except('complaint_id')->query();

        return empty($query) ? '' : '?'.$query;
    }
}
