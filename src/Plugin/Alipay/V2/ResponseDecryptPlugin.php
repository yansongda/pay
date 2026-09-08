<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V2;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\AlipayConfig;
use Yansongda\Pay\Exception\DecryptException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Traits\AlipayTrait;
use Yansongda\Supports\Collection;

use function Yansongda\Artful\should_do_http_request;

class ResponseDecryptPlugin implements PluginInterface
{
    use AlipayTrait;

    /**
     * @throws ContainerException       获取支付宝配置失败
     * @throws DecryptException         密文解密失败或解密后不是合法 JSON 响应
     * @throws InvalidConfigException   未配置 [aes_key] 或密钥格式非法
     * @throws ServiceNotFoundException 获取支付宝配置失败
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        /* @var Rocket $rocket */
        $rocket = $next($rocket);

        if (!should_do_http_request($rocket->getDirection())) {
            return $rocket;
        }

        $destination = $rocket->getDestination();
        if (!$destination instanceof Collection) {
            return $rocket;
        }

        $method = $rocket->getPayload()->get('method');
        $resultKey = str_replace('.', '_', $method).'_response';

        $cipher = $destination->get($resultKey);
        if (!is_string($cipher)) {
            return $rocket;
        }

        /** @var AlipayConfig $config */
        $config = self::getProviderConfig(Pay::PROVIDER_ALIPAY, $rocket->getParams());

        $plain = self::decryptAlipayContents($cipher, $config);

        $data = json_decode($plain, true);
        if (!is_array($data)) {
            throw new DecryptException(Exception::DECRYPT_ALIPAY_ENCRYPTED_DATA_INVALID, '加密解密异常: 支付宝密文解密后不是合法的 JSON 响应');
        }

        Logger::info('[Alipay][ResponseDecryptPlugin] 响应解密成功', ['method' => $method]);

        $rocket->setDestination(new Collection(array_merge(['_sign' => $destination->get('_sign', '')], $data)));

        return $rocket;
    }
}
