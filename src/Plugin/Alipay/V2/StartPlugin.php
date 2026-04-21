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
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws ServiceNotFoundException
     */
    protected function getAppCertSn(AlipayConfig $config): string
    {
        if (!empty($config->getAppPublicCertSn())) {
            return $config->getAppPublicCertSn();
        }

        $path = $config->getAppPublicCertPath();

        if (empty($path)) {
            throw new InvalidConfigException(Exception::CONFIG_ALIPAY_INVALID, '配置异常: 缺少支付宝配置 -- [app_public_cert_path]');
        }

        $ssl = openssl_x509_parse(CertManager::getPublicCert($path));

        if (false === $ssl) {
            throw new InvalidConfigException(Exception::CONFIG_ALIPAY_INVALID, '配置异常: 解析 `app_public_cert_path` 失败');
        }

        $result = $this->getCertSn($ssl['issuer'] ?? [], $ssl['serialNumber'] ?? '');

        $config->setAppPublicCertSn($result);

        return $result;
    }

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws ServiceNotFoundException
     */
    protected function getAlipayRootCertSn(AlipayConfig $config): string
    {
        if (!empty($config->getAlipayRootCertSn())) {
            return $config->getAlipayRootCertSn();
        }

        $path = $config->getAlipayRootCertPath();

        if (empty($path)) {
            throw new InvalidConfigException(Exception::CONFIG_ALIPAY_INVALID, '配置异常: 缺少支付宝配置 -- [alipay_root_cert_path]');
        }

        $sn = '';
        $exploded = explode('-----END CERTIFICATE-----', CertManager::getPublicCert($path));

        foreach ($exploded as $cert) {
            if (empty(trim($cert))) {
                continue;
            }

            $ssl = openssl_x509_parse($cert.'-----END CERTIFICATE-----');

            if (false === $ssl) {
                throw new InvalidConfigException(Exception::CONFIG_ALIPAY_INVALID, '配置异常: 解析 `alipay_root_cert` 失败');
            }

            $detail = $this->formatCert($ssl);

            if ('sha1WithRSAEncryption' == $detail['signatureTypeLN'] || 'sha256WithRSAEncryption' == $detail['signatureTypeLN']) {
                $sn .= $this->getCertSn($detail['issuer'], $detail['serialNumber']).'_';
            }
        }

        $result = substr($sn, 0, -1);

        $config->setAlipayRootCertSn($result);

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
