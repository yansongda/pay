<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay;

use Closure;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Direction\NoHttpRequestDirection;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\AlipayConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Traits\AlipayTrait;

use function Yansongda\Artful\filter_params;

class GatewayCallbackPlugin implements PluginInterface
{
    use AlipayTrait;

    /**
     * 支付宝应用网关验证（`service=alipay.service.check`）：
     * 验签组串仅剔除 `sign`（**保留 `sign_type`**，与异步通知组串的唯一差异）后按字典序直拼 + RSA2 验签；
     * 验签失败不抛异常，按协议返回 `<success>false</success>` 应答；
     * `EventType=verifygw` 时返回 `<success>true</success>` 应答（`biz_content` 附应用公钥主体），
     * 其余网关消息验签通过后原样透传，ack 由业务侧处理.
     *
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][GatewayCallbackPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();

        /** @var AlipayConfig $config */
        $config = self::getProviderConfig(Pay::PROVIDER_ALIPAY, $params);

        $content = filter_params($params, fn ($k, $v) => '' !== $v && 'sign' != $k)
            ->sortKeys()
            ->toString();

        try {
            self::verifyAlipaySign($config, $content, $params['sign'] ?? '');
        } catch (InvalidSignException) {
            Logger::warning('[Alipay][GatewayCallbackPlugin] 网关验证验签失败', ['rocket' => $rocket]);

            return $next(
                $rocket->setDirection(NoHttpRequestDirection::class)
                    ->setDestination($this->buildResponse($config, false))
            );
        }

        // 解析告警调用级抑制，避免污染日志与测试输出
        $xml = simplexml_load_string((string) ($params['biz_content'] ?? ''), null, LIBXML_NOERROR | LIBXML_NOWARNING);

        if (false === $xml) {
            throw new InvalidParamsException(Exception::PARAMS_ALIPAY_GW_BIZ_CONTENT_INVALID, '参数异常: 应用网关 biz_content 不是合法 XML');
        }

        if ('verifygw' !== (string) $xml->EventType) {
            Logger::info('[Alipay][GatewayCallbackPlugin] 插件装载完毕', ['rocket' => $rocket]);

            return $next(
                $rocket->setPayload($params)
                    ->setDirection(NoHttpRequestDirection::class)
                    ->setDestination($rocket->getPayload())
            );
        }

        $rocket->setDirection(NoHttpRequestDirection::class)
            ->setDestination($this->buildResponse($config, true, '<success>true</success><biz_content>'.self::getAlipayAppPublicKey($config).'</biz_content>'));

        Logger::info('[Alipay][GatewayCallbackPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    /**
     * 构造应用网关应答 XML：`<sign>` 为 `<response>` 节点内部文本（$response）原文的 RSA2 签名 + base64.
     *
     * 无论成败均依赖完整商户配置（`app_secret_cert`/`app_public_cert_path`），缺失或签名失败时抛出 InvalidConfigException，不做降级.
     *
     * @throws InvalidConfigException
     */
    private function buildResponse(AlipayConfig $config, bool $success, string $response = ''): ResponseInterface
    {
        if (!$success) {
            $response = '<success>false</success><error_code>VERIFY_FAILED</error_code><biz_content>'.self::getAlipayAppPublicKey($config).'</biz_content>';
        }

        $ok = openssl_sign($response, $sign, self::getAlipayPrivateKey($config), OPENSSL_ALGO_SHA256);

        if (false === $ok || !is_string($sign) || '' === $sign) {
            throw new InvalidConfigException(Exception::CONFIG_ALIPAY_INVALID, '配置异常: 应用网关应答签名失败，请检查 [app_secret_cert]');
        }

        return new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], '<?xml version="1.0" encoding="utf-8"?><alipay><response>'.$response.'</response><sign>'.base64_encode($sign).'</sign><sign_type>RSA2</sign_type></alipay>');
    }
}
