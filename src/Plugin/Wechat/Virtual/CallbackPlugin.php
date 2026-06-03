<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Virtual;

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
use Yansongda\Pay\Config\WechatConfig;
use Yansongda\Pay\Exception\DecryptException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Traits\WechatTrait;
use Yansongda\Supports\Collection;

/**
 * @see https://developers.weixin.qq.com/miniprogram/dev/platform-capabilities/business-capabilities/virtual-payment.html#_2-4-%E6%B6%88%E6%81%AF%E6%8E%A8%E9%80%81
 * @see https://developers.weixin.qq.com/doc/service/guide/dev/push/encryption.html
 */
class CallbackPlugin implements PluginInterface
{
    use WechatTrait;

    /**
     * @throws ContainerException
     * @throws DecryptException
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws InvalidSignException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Virtual][CallbackPlugin] 插件开始装载', ['rocket' => $rocket]);

        $this->init($rocket);

        $params = $rocket->getParams();

        /** @var WechatConfig $config */
        $config = self::getProviderConfig('wechat', $params);

        $body = (string) $rocket->getDestination()->getBody();
        $parsed = $this->parseBody($body);

        $request = $rocket->getDestinationOrigin();
        $query = $request instanceof ServerRequestInterface ? $request->getQueryParams() : [];

        $this->verifySign(
            $config,
            $parsed['Encrypt'] ?? '',
            $query['msg_signature'] ?? '',
            $query['timestamp'] ?? '',
            $query['nonce'] ?? ''
        );

        $decrypted = $this->decryptMessage($config, $parsed['Encrypt']);

        $rocket->setDirection(NoHttpRequestDirection::class)
            ->setPayload(new Collection($parsed))
            ->setDestination(new Collection($decrypted));

        Logger::info('[Wechat][Virtual][CallbackPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    /**
     * @throws InvalidParamsException
     */
    protected function init(Rocket $rocket): void
    {
        $request = $rocket->getParams()['_request'] ?? null;
        $params = $rocket->getParams()['_params'] ?? [];

        if (!$request instanceof ServerRequestInterface) {
            throw new InvalidParamsException(Exception::PARAMS_CALLBACK_REQUEST_INVALID, '参数异常: 微信虚拟支付回调参数不正确');
        }

        $rocket->setDestination(clone $request)
            ->setDestinationOrigin($request)
            ->setParams($params);
    }

    protected function parseBody(string $body): array
    {
        $json = json_decode($body, true);

        if (is_array($json) && isset($json['Encrypt'])) {
            return $json;
        }

        $xml = simplexml_load_string($body, 'SimpleXMLElement', LIBXML_NOCDATA);

        if (false !== $xml) {
            $result = json_decode(json_encode($xml), true);

            if (is_array($result)) {
                return $result;
            }
        }

        return [];
    }

    /**
     * @throws InvalidConfigException
     * @throws InvalidSignException
     */
    protected function verifySign(WechatConfig $config, string $encrypt, string $signature, string $timestamp, string $nonce): void
    {
        $token = $config->getVirtualPay()->getCallbackToken();

        if (empty($token)) {
            throw new InvalidConfigException(Exception::CONFIG_WECHAT_INVALID, '配置异常: 缺少微信虚拟支付配置 -- [virtual_pay.callback_token]');
        }

        if (empty($signature)) {
            throw new InvalidSignException(Exception::SIGN_EMPTY, '签名异常: 微信虚拟支付回调签名为空');
        }

        $arr = [$token, $timestamp, $nonce, $encrypt];
        sort($arr, SORT_STRING);

        if (!hash_equals($signature, sha1(implode('', $arr)))) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: 验证微信虚拟支付回调签名失败');
        }
    }

    /**
     * @throws DecryptException
     * @throws InvalidConfigException
     */
    protected function decryptMessage(WechatConfig $config, string $encrypt): array
    {
        $encodingAesKey = $config->getVirtualPay()->getEncodingAesKey();

        if (empty($encodingAesKey)) {
            throw new InvalidConfigException(Exception::CONFIG_WECHAT_INVALID, '配置异常: 缺少微信虚拟支付配置 -- [virtual_pay.encoding_aes_key]');
        }

        $key = base64_decode($encodingAesKey.'=');
        $iv = substr($key, 0, 16);

        $ciphertext = base64_decode($encrypt);

        $decrypted = openssl_decrypt($ciphertext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);

        if (false === $decrypted) {
            throw new DecryptException(Exception::DECRYPT_WECHAT_ENCRYPTED_DATA_INVALID, '加解密异常: 解密微信虚拟支付回调数据失败');
        }

        // Remove PKCS7 padding (K=32, per WeChat's variant PKCS7 spec)
        $pad = ord($decrypted[strlen($decrypted) - 1]);

        if ($pad < 1 || $pad > 32) {
            throw new DecryptException(Exception::DECRYPT_WECHAT_ENCRYPTED_DATA_INVALID, '加解密异常: PKCS7 padding 值无效');
        }

        // Verify all padding bytes are consistent
        for ($i = 0; $i < $pad; ++$i) {
            if (ord($decrypted[strlen($decrypted) - 1 - $i]) !== $pad) {
                throw new DecryptException(Exception::DECRYPT_WECHAT_ENCRYPTED_DATA_INVALID, '加解密异常: PKCS7 padding 验证失败');
            }
        }

        $decrypted = substr($decrypted, 0, -$pad);

        // Extract: 16 random bytes + 4 bytes msg length (big-endian) + msg + appId
        if (strlen($decrypted) < 20) {
            throw new DecryptException(Exception::DECRYPT_WECHAT_ENCRYPTED_DATA_INVALID, '加解密异常: 解密后数据格式不正确');
        }

        $content = substr($decrypted, 16);
        $msgLen = unpack('N', substr($content, 0, 4))[1];
        $message = substr($content, 4, $msgLen);
        $appId = substr($content, 4 + $msgLen);

        // Verify appId
        $expectedAppId = $config->getMiniAppId();

        if ($appId !== $expectedAppId) {
            throw new DecryptException(Exception::DECRYPT_WECHAT_ENCRYPTED_DATA_INVALID, '加解密异常: 微信虚拟支付回调 appId 不匹配');
        }

        // Parse the decrypted XML
        $xml = simplexml_load_string($message, 'SimpleXMLElement', LIBXML_NOCDATA);

        if (false === $xml) {
            throw new DecryptException(Exception::DECRYPT_WECHAT_ENCRYPTED_DATA_INVALID, '加解密异常: 解密后的数据格式不正确');
        }

        return json_decode(json_encode($xml), true);
    }
}
