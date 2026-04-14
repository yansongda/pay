<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Airwallex\V1\Pay;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;

use function Yansongda\Pay\get_airwallex_request_id;

class CancelPlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Airwallex][V1][Pay][CancelPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();
        $id = $payload->get('id', $payload->get('payment_intent_id'));

        if (empty($id)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: Airwallex 取消缺少必要参数 -- [id] or [payment_intent_id]');
        }

        $rocket->mergePayload([
            '_method' => 'POST',
            '_url' => 'api/v1/pa/payment_intents/'.$id.'/cancel',
            'request_id' => $payload->get('request_id', get_airwallex_request_id()),
            'cancellation_reason' => $payload->get('cancellation_reason'),
        ]);

        Logger::info('[Airwallex][V1][Pay][CancelPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
