<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Douyin\V1\Pay;

use Closure;
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
use Yansongda\Pay\Pay;
use Yansongda\Pay\Traits\DouyinTrait;
use Yansongda\Supports\Collection;

use function Yansongda\Artful\filter_params;

/**
 * 抖音 JSAPI（小程序）下单插件：产出前端调起参数（不发 HTTP 请求）.
 *
 * 透传业务字段（outOrderNo/totalAmount/skuList/orderEntrySchema 等官方 camelCase 字段），
 * 按官方通用交易系统规范生成 `byteAuthorization` 请求头值，返回 `{data, byteAuthorization}`，
 * 供前端 `tt.requestOrder(data, byteAuthorization)` 调起下单。
 *
 * @see https://developer.open-douyin.com/docs/resource/zh-CN/mini-app/develop/server/payment/trade-system/general/order/request-order-data-sign
 */
class InvokePlugin implements PluginInterface
{
    use DouyinTrait;

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Douyin][V1][Pay][InvokePlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $payload = $rocket->getPayload();

        /** @var DouyinConfig $config */
        $config = self::getProviderConfig(Pay::PROVIDER_DOUYIN, $params);

        $fields = filter_params($payload)->all();

        if (empty($fields)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 抖音 JSAPI 下单签名，缺少业务参数（如 `outOrderNo`/`totalAmount`/`skuList` 等）');
        }

        $data = json_encode($fields, JSON_UNESCAPED_UNICODE);

        $byteAuthorization = self::getDouyinTradeSign($config, 'POST', '/requestOrder', $data);

        // 客户端签名场景：不发送 HTTP 请求，直接返回签名数据给前端
        $rocket->setDirection(NoHttpRequestDirection::class);

        Logger::info('[Douyin][V1][Pay][InvokePlugin] 插件装载完毕', ['rocket' => $rocket]);

        /** @var Rocket $rocket */
        $rocket = $next($rocket);
        $rocket->setDestination(new Collection(['data' => $data, 'byteAuthorization' => $byteAuthorization]));

        return $rocket;
    }
}
