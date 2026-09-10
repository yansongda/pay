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
 * @see https://github.com/Belos10/DiningOrder CallMapiSDKInterface
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
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 缺少翼支付 `_path`，可能插件用错顺序，应该先使用业务插件');
        }

        $bizContent = $payload->except(['_path', '_method', '_url', 'sign'])->all();
        $commonParams = [
            'institutionType' => $payload->get('institutionType', $config->getInstitutionType()),
            'institutionCode' => $payload->get('institutionCode', $config->getInstitutionCode()),
        ];

        $envelope = [
            'path' => (string) $path,
            'commonParams' => json_encode($commonParams, JSON_UNESCAPED_UNICODE),
            'bizContent' => json_encode($bizContent, JSON_UNESCAPED_UNICODE),
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
