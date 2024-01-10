<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V2;

use Closure;
use Yansongda\Artful\Contract\ConfigInterface;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;

use function Yansongda\Pay\get_alipay_config;
use function Yansongda\Pay\get_public_cert;
use function Yansongda\Pay\get_tenant;

class StartPlugin implements PluginInterface
{
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
        $tenant = get_tenant($params);
        $config = get_alipay_config($params);

        return [
            'app_id' => $config['app_id'] ?? '',
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
            'app_cert_sn' => $this->getAppCertSn($tenant, $config),
            'alipay_root_cert_sn' => $this->getAlipayRootCertSn($tenant, $config),
            'biz_content' => [],
        ];
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

    protected function getAppAuthToken(array $params, array $config): string
    {
        if (!empty($params['_app_auth_token'])) {
            return $params['_app_auth_token'];
        }

        return $config['app_auth_token'] ?? '';
    }

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws ServiceNotFoundException
     */
    protected function getAppCertSn(string $tenant, array $config): string
    {
        if (!empty($config['app_public_cert_sn'])) {
            return $config['app_public_cert_sn'];
        }

        $path = $config['app_public_cert_path'] ?? null;

        if (is_null($path)) {
            throw new InvalidConfigException(Exception::CONFIG_ALIPAY_INVALID, '配置异常: 缺少支付宝配置 -- [app_public_cert_path]');
        }

        $ssl = openssl_x509_parse(get_public_cert($path));

        if (false === $ssl) {
            throw new InvalidConfigException(Exception::CONFIG_ALIPAY_INVALID, 'Parse `app_public_cert_path` Error');
        }

        $result = $this->getCertSn($ssl['issuer'] ?? [], $ssl['serialNumber'] ?? '');

        Pay::get(ConfigInterface::class)->set('alipay.'.$tenant.'.app_public_cert_sn', $result);

        return $result;
    }

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws ServiceNotFoundException
     */
    protected function getAlipayRootCertSn(string $tenant, array $config): string
    {
        if (!empty($config['alipay_root_cert_sn'])) {
            return $config['alipay_root_cert_sn'];
        }

        $path = $config['alipay_root_cert_path'] ?? null;

        if (is_null($path)) {
            throw new InvalidConfigException(Exception::CONFIG_ALIPAY_INVALID, 'Missing Alipay Config -- [alipay_root_cert_path]');
        }

        $sn = '';
        $exploded = explode('-----END CERTIFICATE-----', file_get_contents($path));

        foreach ($exploded as $cert) {
            if (empty(trim($cert))) {
                continue;
            }

            $ssl = openssl_x509_parse($cert.'-----END CERTIFICATE-----');

            if (false === $ssl) {
                throw new InvalidConfigException(Exception::CONFIG_ALIPAY_INVALID, 'Invalid alipay_root_cert');
            }

            $detail = $this->formatCert($ssl);

            if ('sha1WithRSAEncryption' == $detail['signatureTypeLN'] || 'sha256WithRSAEncryption' == $detail['signatureTypeLN']) {
                $sn .= $this->getCertSn($detail['issuer'], $detail['serialNumber']).'_';
            }
        }

        $result = substr($sn, 0, -1);

        Pay::get(ConfigInterface::class)->set('alipay.'.$tenant.'.alipay_root_cert_sn', $result);

        return $result;
    }

    protected function getCertSn(array $issuer, string $serialNumber): string
    {
        return md5($this->array2string(array_reverse($issuer)).$serialNumber);
    }

    protected function array2string(array $array): string
    {
        $string = [];

        foreach ($array as $key => $value) {
            $string[] = $key.'='.$value;
        }

        return implode(',', $string);
    }

    protected function formatCert(array $ssl): array
    {
        if (str_starts_with($ssl['serialNumber'] ?? '', '0x')) {
            $ssl['serialNumber'] = $this->hex2dec($ssl['serialNumberHex'] ?? '');
        }

        return $ssl;
    }

    protected function hex2dec(string $hex): string
    {
        $dec = '0';
        $len = strlen($hex);

        for ($i = 1; $i <= $len; ++$i) {
            $dec = bcadd(
                $dec,
                bcmul(strval(hexdec($hex[$i - 1])), bcpow('16', strval($len - $i), 0), 0),
                0
            );
        }

        return $dec;
    }
}
