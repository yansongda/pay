<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Allinpay;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Packer\JsonPacker;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\AllinpayConfig;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Traits\AllinpayTrait;
use Yansongda\Supports\Str;

/**
 * @see https://prodoc.allinpay.com/doc/256/ 通联支付公共请求参数（cusid/appid/orgid/signtype/randomstr/version）
 */
class StartPlugin implements PluginInterface
{
    use AllinpayTrait;

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::info('[Allinpay][StartPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();

        /** @var AllinpayConfig $config */
        $config = self::getProviderConfig(Pay::PROVIDER_ALLINPAY, $params);

        $common = [
            'cusid' => $config->getCusid(),
            'appid' => $config->getAppid(),
            'signtype' => 'RSA',
            'randomstr' => Str::random(20),
        ];

        // orgid 官方为可选参数（"共享集团号/代理商参数时必填"），仅在配置后才发送
        if (!empty($config->getOrgid())) {
            $common['orgid'] = $config->getOrgid();
        }

        $rocket->setPacker(JsonPacker::class)
            ->mergePayload(array_merge($params, $common));

        Logger::info('[Allinpay][StartPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
