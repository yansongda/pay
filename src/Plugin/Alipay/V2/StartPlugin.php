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
use Yansongda\Pay\CertManager;
use Yansongda\Pay\Config\AlipayConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Traits\AlipayTrait;

class StartPlugin implements PluginInterface
{
    use AlipayTrait;

    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     * @throws InvalidConfigException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][StartPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload($this->getPayload($rocket->getParams()));

        Logger::info('[Alipay][StartPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     * @throws InvalidConfigException
     */
    protected function getPayload(array $params): array
    {
        /** @var AlipayConfig $config */
        $config = self::getProviderConfig('alipay', $params);

        return [
            'app_id' => $config->getAppId(),
            'method' => '',
            'format' => 'JSON',
            'return_url' => $this->getReturnUrl($params, $config),
            'charset' => 'utf-8',
            'sign_type' => 'RSA2',
            'sign' => '',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0',
            'notify_url' => $this->getNotifyUrl($params, $config),
            'app_auth_token' => $this->getAppAuthToken($params, $config),
            'app_cert_sn' => $this->getAppCertSn($config),
            'alipay_root_cert_sn' => $this->getAlipayRootCertSn($config),
            'biz_content' => [],
        ];
    }

    protected function getReturnUrl(array $params, AlipayConfig $config): string
    {
        if (!empty($params['_return_url'])) {
            return $params['_return_url'];
        }

        return $config->getReturnUrl() ?? '';
    }

    protected function getNotifyUrl(array $params, AlipayConfig $config): string
    {
        if (!empty($params['_notify_url'])) {
            return $params['_notify_url'];
        }

        return $config->getNotifyUrl() ?? '';
    }

    protected function getAppAuthToken(array $params, AlipayConfig $config): string
    {
        if (!empty($params['_app_auth_token'])) {
            return $params['_app_auth_token'];
        }

        return $config->getAppAuthToken() ?? '';
    }

    /**
     * @throws InvalidConfigException 缺少证书配置或证书解析失败
     */
    protected function getAppCertSn(AlipayConfig $config): string
    {
        $path = $config->getAppPublicCertPath();

        if (empty($path)) {
            throw new InvalidConfigException(Exception::CONFIG_ALIPAY_INVALID, '配置异常: 缺少支付宝配置 -- [app_public_cert_path]');
        }

        return CertManager::alipayGetAppCertSn($path);
    }

    /**
     * @throws InvalidConfigException 缺少证书配置或证书解析失败
     */
    protected function getAlipayRootCertSn(AlipayConfig $config): string
    {
        $path = $config->getAlipayRootCertPath();

        if (empty($path)) {
            throw new InvalidConfigException(Exception::CONFIG_ALIPAY_INVALID, '配置异常: 缺少支付宝配置 -- [alipay_root_cert_path]');
        }

        return CertManager::alipayGetRootCertSn($path);
    }
}
