<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V3;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\CertManager;
use Yansongda\Pay\Config\AlipayConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Traits\AlipayTrait;

use function Yansongda\Artful\should_do_http_request;

class VerifySignaturePlugin implements PluginInterface
{
    use AlipayTrait;

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidSignException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        /* @var Rocket $rocket */
        $rocket = $next($rocket);

        Logger::debug('[Alipay][V3][VerifySignaturePlugin] 插件开始装载', ['rocket' => $rocket]);

        if (!should_do_http_request($rocket->getDirection()) || is_null($rocket->getDestinationOrigin())) {
            return $rocket;
        }

        $this->verifyAlipayV3ResponseSign($rocket);

        Logger::info('[Alipay][V3][VerifySignaturePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }

    /**
     * 验证支付宝 V3 同步响应签名.
     *
     * 验签策略对齐官方 SDK：HTTP 200 强制验签；其余响应存在 `alipay-signature` 时验签（防篡改），
     * 无签名直接放行进入错误处理（不做时间戳校验，避免无签且时间戳过期的错误响应被时间戳异常拦截）.
     *
     * @see https://github.com/alipay/alipay-sdk-php-all `v3/src/Util/AlipayConfigUtil::verifyResponse()`
     *
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidSignException
     * @throws ServiceNotFoundException
     */
    protected function verifyAlipayV3ResponseSign(Rocket $rocket): void
    {
        $response = $rocket->getDestinationOrigin();

        if (!$response instanceof ResponseInterface) {
            return;
        }

        $timestamp = $response->getHeaderLine('alipay-timestamp');
        $nonce = $response->getHeaderLine('alipay-nonce');
        $sign = $response->getHeaderLine('alipay-signature');
        $body = (string) $response->getBody();

        // 非 200 响应：无签名直接放行进入错误处理（对齐官方「有签才验」）
        if (200 !== $response->getStatusCode() && '' === $sign) {
            return;
        }

        /** @var AlipayConfig $config */
        $config = self::getProviderConfig('alipay', $rocket->getParams());

        // 证书模式：按 `alipay-sn` 匹配本地支付宝公钥证书 SN。
        // 有意偏差：`alipay-sn` 缺失或与本地证书不匹配一律抛异常（官方会回落缓存第一个公钥，此处更严格）
        if (!empty($config->getAlipayPublicCertPath())
            && $response->getHeaderLine('alipay-sn') !== CertManager::alipayGetAppCertSn($config->getAlipayPublicCertPath())) {
            throw new InvalidSignException(
                Exception::SIGN_ERROR,
                '签名异常: 支付宝公钥证书已过期/不匹配，请重新下载最新支付宝公钥证书并替换',
                ['alipay_sn' => $response->getHeaderLine('alipay-sn'), 'headers' => $response->getHeaders(), 'body' => $body]
            );
        }

        // 时间戳校验（13 位毫秒，±300 秒）：强制路径无条件校验；非强制路径有签才校验
        self::verifyAlipayV3Timestamp($timestamp);

        // 组串：`${timestamp}\n${nonce}\n${body}\n`（末尾必带 \n）
        $content = $timestamp."\n".$nonce."\n".$body."\n";

        self::verifyAlipayV3Sign($config, $content, $sign);
    }
}
