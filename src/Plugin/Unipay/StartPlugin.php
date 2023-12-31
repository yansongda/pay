<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Unipay;

use Closure;
use Yansongda\Pay\Contract\ConfigInterface;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Rocket;

use function Yansongda\Pay\get_tenant;
use function Yansongda\Pay\get_unipay_config;

class StartPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     * @throws InvalidConfigException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Unipay][StartPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = get_unipay_config($params);
        $tenant = get_tenant($params);

        $rocket->mergePayload(array_merge($params, [
            'version' => '5.1.0',
            'encoding' => 'utf-8',
            'backUrl' => $this->getNotifyUrl($params, $config),
            'currencyCode' => '156',
            'accessType' => '0',
            'signature' => '',
            'signMethod' => '01',
            'merId' => $config['mch_id'] ?? '',
            'frontUrl' => $this->getReturnUrl($params, $config),
            'certId' => $this->getCertId($tenant, $config),
        ]));

        Logger::info('[Unipay][StartPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws ServiceNotFoundException
     */
    public function getCertId(string $tenant, array $config): string
    {
        if (!empty($config['certs']['cert_id'])) {
            return $config['certs']['cert_id'];
        }

        $certs = $this->getCerts($config);
        $ssl = openssl_x509_parse($certs['cert'] ?? '');

        if (false === $ssl) {
            throw new InvalidConfigException(Exception::CONFIG_UNIPAY_INVALID, '配置异常: 解析银联 `mch_cert_path` 失败，请检查参数是否正确');
        }

        $certs['cert_id'] = $ssl['serialNumber'] ?? '';

        Pay::get(ConfigInterface::class)->set('unipay.'.$tenant.'.certs', $certs);

        return $certs['cert_id'];
    }

    protected function getReturnUrl(array $params, array $config): string
    {
        if (!empty($params['_return_url'])) {
            return $params['_return_url'];
        }

        return $config['return_url'] ?? '';
    }

    protected function getNotifyUrl(array $params, array $config): string
    {
        if (!empty($params['_notify_url'])) {
            return $params['_notify_url'];
        }

        return $config['notify_url'] ?? '';
    }

    /**
     * @return array ['cert' => 公钥, 'pkey' => 私钥, 'extracerts' => array]
     *
     * @throws InvalidConfigException
     */
    protected function getCerts(array $config): array
    {
        $path = $config['mch_cert_path'] ?? null;
        $password = $config['mch_cert_password'] ?? null;

        if (is_null($path) || is_null($password)) {
            throw new InvalidConfigException(Exception::CONFIG_UNIPAY_INVALID, '配置异常: 缺少银联配置 -- [mch_cert_path] or [mch_cert_password]');
        }

        if (false === openssl_pkcs12_read(file_get_contents($path), $certs, $password)) {
            throw new InvalidConfigException(Exception::CONFIG_UNIPAY_INVALID, '配置异常: 读取银联 `mch_cert_path` 失败，请确认参数是否正确');
        }

        return $certs;
    }
}
