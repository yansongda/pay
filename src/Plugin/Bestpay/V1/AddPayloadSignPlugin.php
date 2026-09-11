<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Bestpay\V1;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\BestpayConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Traits\BestpayTrait;
use Yansongda\Supports\Collection;

/**
 * 组装 sdkRequest 信封：path / commonParams / bizContent / sign.
 *
 * bizContent 过滤 null 值字段，对齐官方 Java SDK fastjson 默认不序列化 null 的行为.
 *
 * @see https://github.com/Belos10/DiningOrder/blob/master/src/main/java/com/example/utils/payUtil/Demo/CallMapiSDKInterface.java CallMapiSDKInterface.getCallRequestParam/sign
 * @see https://mapi.bestpay.com.cn/gapi/telecomPortal/getApiDocument?auxiliaryCode=PublicParameters1007 翼支付公共请求参数
 */
class AddPayloadSignPlugin implements PluginInterface
{
    use BestpayTrait;

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Bestpay][V1][AddPayloadSignPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();

        /** @var BestpayConfig $config */
        $config = self::getProviderConfig(Pay::PROVIDER_BESTPAY, $params);
        $payload = $rocket->getPayload() ?? new Collection();

        $path = $payload->get('_path');

        if (empty($path)) {
            throw new InvalidParamsException(Exception::PARAMS_BESTPAY_PATH_MISSING, '参数异常: 缺少翼支付 `_path`，可能插件用错顺序，应该先使用业务插件');
        }

        $bizContent = array_filter(
            $payload->all(),
            static fn (mixed $v, string $k): bool => !is_null($v)
                && !str_starts_with($k, '_')
                && !in_array($k, ['sign', 'institutionType', 'institutionCode'], true),
            ARRAY_FILTER_USE_BOTH
        );

        $commonParams = [
            'institutionType' => $payload->get('institutionType', $config->getInstitutionType()),
            'institutionCode' => $payload->get('institutionCode', $config->getInstitutionCode()),
        ];

        $envelope = [
            'path' => (string) $path,
            // JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES 对齐官方 fastjson 默认序列化
            // （非 ASCII 与 `/` 均不转义），避免含 URL 的字段被转义为 `\/` 影响签名一致性与服务端处理。
            'commonParams' => json_encode($commonParams, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'bizContent' => json_encode($bizContent, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ];

        $envelope['sign'] = self::signBestpayContent(
            $config,
            self::getBestpaySignContent($envelope)
        );

        $rocket->setPayload(new Collection($envelope));

        Logger::info('[Bestpay][V1][AddPayloadSignPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
