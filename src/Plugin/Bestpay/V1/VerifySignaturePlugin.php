<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Bestpay\V1;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\BestpayConfig;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Traits\BestpayTrait;
use Yansongda\Supports\Collection;

use function Yansongda\Artful\should_do_http_request;

/**
 * 响应验签：解析后的 JSON 报文 + 平台公钥（SHA1/SHA256 双试）.
 *
 * @see https://render.bestpay.cn/open-developers/index.html#/documentCenterLayout/developGuide 翼支付官方文档（开发指南）
 */
class VerifySignaturePlugin implements PluginInterface
{
    use BestpayTrait;

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidResponseException
     * @throws ServiceNotFoundException
     * @throws InvalidSignException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        /* @var Rocket $rocket */
        $rocket = $next($rocket);

        Logger::debug('[Bestpay][V1][VerifySignaturePlugin] 插件开始装载', ['rocket' => $rocket]);

        if (should_do_http_request($rocket->getDirection())) {
            $destination = $rocket->getDestination();

            if ($destination instanceof Collection && $destination->isNotEmpty()) {
                /** @var BestpayConfig $config */
                $config = self::getProviderConfig(Pay::PROVIDER_BESTPAY, $rocket->getParams());

                self::verifyBestpaySign($config, $destination->all());
            }
        }

        Logger::info('[Bestpay][V1][VerifySignaturePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }
}
