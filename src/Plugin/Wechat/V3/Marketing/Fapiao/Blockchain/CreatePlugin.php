<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\Marketing\Fapiao\Blockchain;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\WechatConfig;
use Yansongda\Pay\Exception\DecryptException;
use Yansongda\Pay\Traits\WechatTrait;
use Yansongda\Supports\Collection;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/fapiao/fapiao-applications/issue-fapiao-applications.html
 */
class CreatePlugin implements PluginInterface
{
    use WechatTrait;

    /**
     * @throws ContainerException
     * @throws DecryptException
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][V3][Marketing][Fapiao][Blockchain][CreatePlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $payload = $rocket->getPayload();
        $config = self::getProviderConfig('wechat', $params);

        $rocket->mergePayload(array_merge([
            '_method' => 'POST',
            '_url' => 'v3/new-tax-control-fapiao/fapiao-applications',
        ], $this->encryptSensitiveData($payload, $params, $config)));

        Logger::info('[Wechat][V3][Marketing][Fapiao][Blockchain][CreatePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     * @throws DecryptException
     */
    protected function encryptSensitiveData(?Collection $payload, array $params, WechatConfig $config): array
    {
        $data['_serial_no'] = self::getWechatSerialNo($params);
        $publicKey = self::getWechatPublicKey($config, $data['_serial_no']);

        $phone = $payload?->get('buyer_information.phone') ?? null;
        $email = $payload?->get('buyer_information.email') ?? null;

        if (!is_null($phone)) {
            $data['buyer_information']['phone'] = self::encryptWechatContents($phone, $publicKey);
        }

        if (!is_null($email)) {
            $data['buyer_information']['email'] = self::encryptWechatContents($email, $publicKey);
        }

        return $data;
    }
}
