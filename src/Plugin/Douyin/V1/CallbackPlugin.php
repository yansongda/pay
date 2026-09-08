<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Douyin\V1;

use Closure;
use Psr\Http\Message\ServerRequestInterface;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Direction\NoHttpRequestDirection;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\DouyinConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Traits\DouyinTrait;
use Yansongda\Supports\Collection;

/**
 * 抖音回调插件：统一处理 payment（支付结果）/refund（退款结果）/pre_create_refund（退款申请）三类回调，
 * 验签后解析 body 中的 msg 并以 Collection 返回，业务方按 body.type 分发处理。
 *
 * @see https://developer.open-douyin.com/docs/resource/zh-CN/mini-app/develop/server/payment/trade-system/general/order/notify-payment-result
 * @see https://developer.open-douyin.com/docs/resource/zh-CN/mini-app/develop/server/payment/trade-system/general/refund/refund-notify
 * @see https://developer.open-douyin.com/docs/resource/zh-CN/mini-app/develop/server/locallife/general-ability/self-operated-trading/refund/refund-callback
 */
class CallbackPlugin implements PluginInterface
{
    use DouyinTrait;

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws InvalidSignException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Douyin][V1][CallbackPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();

        /** @var DouyinConfig $config */
        $config = self::getProviderConfig(Pay::PROVIDER_DOUYIN, $params);

        $request = $params['_request'] ?? null;

        if (!$request instanceof ServerRequestInterface) {
            throw new InvalidParamsException(Exception::PARAMS_CALLBACK_REQUEST_INVALID, '参数异常: 抖音回调参数不正确，缺少 `_request` 或其不是 ServerRequestInterface 实例');
        }

        self::verifyDouyinTradeSign($request, $config);

        $body = json_decode((string) $request->getBody(), true);
        $type = is_array($body) ? ($body['type'] ?? null) : null;

        if (!is_string($type) || '' === $type) {
            throw new InvalidParamsException(Exception::PARAMS_CALLBACK_REQUEST_INVALID, '参数异常: 抖音回调 body 非法或缺少非空的 `type` 字段');
        }

        $msg = is_string($body['msg'] ?? null) ? json_decode($body['msg'], true) : null;

        if (!is_array($msg)) {
            throw new InvalidParamsException(Exception::PARAMS_CALLBACK_REQUEST_INVALID, '参数异常: 抖音回调 `msg` 解析失败');
        }

        $rocket->setPayload(new Collection($msg))
            ->setDirection(NoHttpRequestDirection::class)
            ->setDestination($rocket->getPayload());

        Logger::info('[Douyin][V1][CallbackPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
