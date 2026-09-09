<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V2;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\Exception;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\AlipayConfig;
use Yansongda\Pay\Exception\DecryptException;
use Yansongda\Pay\Exception\Exception as PayException;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Traits\AlipayTrait;
use Yansongda\Supports\Collection;

use function Yansongda\Artful\should_do_http_request;

class VerifySignaturePlugin implements PluginInterface
{
    use AlipayTrait;

    /**
     * 验签通过后，若 destination 含 `_cipher` 密文标记（由 ResponsePlugin 拆包时写入），
     * 则在此解密并拆包交付（官方要求验签先于解密，
     * 解密原语严格门控于 verifyAlipaySign() 正常返回之后，未认证密文不可达）。
     *
     * @throws ContainerException
     * @throws DecryptException         验签通过后密文解密失败或解密后不是合法 JSON 响应
     * @throws InvalidConfigException   验签通过后因未配置 [aes_key] 或密钥格式非法而无法解密
     * @throws ServiceNotFoundException
     * @throws InvalidSignException
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        /* @var Rocket $rocket */
        $rocket = $next($rocket);

        Logger::debug('[Alipay][VerifySignaturePlugin] 插件开始装载', ['rocket' => $rocket]);

        if (!should_do_http_request($rocket->getDirection())) {
            return $rocket;
        }

        $destination = $rocket->getDestination();

        if ((!$destination instanceof Collection) || empty($result = $destination->except('_sign')->all())) {
            throw new InvalidParamsException(Exception::RESPONSE_EMPTY, '参数异常: 支付宝验证签名时待验签参数不正确', $destination);
        }

        /** @var AlipayConfig $config */
        $config = self::getProviderConfig(Pay::PROVIDER_ALIPAY, $rocket->getParams());

        // 加密响应场景：ResponsePlugin 以固定 `_cipher` 协议键交付密文（与 `_sign` 同构），
        // 签名源为带双引号的密文原文，与官方 SDK 取串行为一致（JSON_UNESCAPED_SLASHES 保证
        // base64 密文中的 `/` 不被转义）；其余场景签名源保持原状
        $cipher = $destination->get('_cipher');

        $signContent = is_string($cipher)
            ? json_encode($cipher, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : json_encode($result, JSON_UNESCAPED_UNICODE);

        self::verifyAlipaySign($config, $signContent, $destination->get('_sign', ''));

        // 验签已通过：密文场景在此解密拆包（encrypt-then-MAC，解密原语不可达于未认证密文）
        if (is_string($cipher)) {
            $data = json_decode(self::decryptAlipayContents($cipher, $config), true);

            if (!is_array($data)) {
                throw new DecryptException(PayException::DECRYPT_ALIPAY_ENCRYPTED_DATA_INVALID, '加密解密异常: 支付宝密文解密后不是合法的 JSON 响应');
            }

            Logger::info('[Alipay][VerifySignaturePlugin] 响应解密成功', ['method' => $rocket->getPayload()?->get('method')]);

            $rocket->setDestination(new Collection(array_merge(['_sign' => $destination->get('_sign', '')], $data)));
        }

        Logger::info('[Alipay][VerifySignaturePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }
}
