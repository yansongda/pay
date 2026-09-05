<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Douyin\V1\Refund;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;

use function Yansongda\Artful\filter_params;

/**
 * 退款审核：透传 refund_id/refund_audit_status(1 同意/2 拒绝)/deny_message 等业务字段，业务字段均无 app_id，不注入.
 */
class AuditPlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Douyin][V1][Refund][AuditPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();

        if (is_null($payload) || filter_params($payload)->isEmpty()) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 抖音退款审核，缺少必要的业务参数');
        }

        $rocket->mergePayload([
            '_method' => 'POST',
            '_url' => '/api/trade_basic/v1/developer/refund_audit_callback/',
        ]);

        Logger::info('[Douyin][V1][Refund][AuditPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
