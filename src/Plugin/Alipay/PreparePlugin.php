<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

class PreparePlugin implements PluginInterface
{
    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::info('[alipay][PreparePlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload($this->getPayload($rocket->getParams()));

        Logger::info('[alipay][PreparePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     */
    protected function getPayload(array $params): array
    {
        return [
            'app_id' => get_alipay_config($params)->get('app_id', ''),
            'method' => '',
            'format' => 'JSON',
            'return_url' => $this->getReturnUrl($params),
            'charset' => 'utf-8',
            'sign_type' => 'RSA2',
            'sign' => '',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0',
            'notify_url' => $this->getNotifyUrl($params),
            'app_auth_token' => '',
            'app_cert_sn' => $this->getAppCertSn($params),
            'alipay_root_cert_sn' => $this->getAlipayRootCertSn($params),
            'biz_content' => [],
        ];
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    protected function getReturnUrl(array $params): string
    {
        if (!empty($params['_return_url'])) {
            return $params['_return_url'];
        }

        return get_alipay_config($params)->get('return_url', '');
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    protected function getNotifyUrl(array $params): string
    {
        if (!empty($params['_notify_url'])) {
            return $params['_notify_url'];
        }

        return get_alipay_config($params)->get('notify_url', '');
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     */
    protected function getAppCertSn(array $params): string
    {
        $path = get_alipay_config($params)->get('app_public_cert_path');

        if (is_null($path)) {
            throw new InvalidConfigException(Exception::ALIPAY_CONFIG_ERROR, 'Missing Alipay Config -- [app_public_cert_path]');
        }

        $cert = file_get_contents($path);
        $ssl = openssl_x509_parse($cert);

        return $this->getCertSn($ssl['issuer'], $ssl['serialNumber']);
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    protected function getAlipayRootCertSn(array $params): string
    {
        $path = get_alipay_config($params)->get('alipay_root_cert_path');

        if (is_null($path)) {
            throw new InvalidConfigException(Exception::ALIPAY_CONFIG_ERROR, 'Missing Alipay Config -- [alipay_root_cert_path]');
        }

        $sn = '';
        $exploded = explode('-----END CERTIFICATE-----', file_get_contents($path));

        foreach ($exploded as $cert) {
            if (empty(trim($cert))) {
                continue;
            }

            $ssl = openssl_x509_parse($cert.'-----END CERTIFICATE-----');

            if (false === $ssl) {
                throw new InvalidConfigException(Exception::ALIPAY_CONFIG_ERROR, 'Invalid alipay_root_cert');
            }

            $detail = $this->formatCert($ssl);

            if ('sha1WithRSAEncryption' == $detail['signatureTypeLN'] || 'sha256WithRSAEncryption' == $detail['signatureTypeLN']) {
                $sn .= $this->getCertSn($detail['issuer'], $detail['serialNumber']).'_';
            }
        }

        return substr($sn, 0, -1);
    }

    protected function getCertSn(array $issuer, string $serialNumber): string
    {
        return md5(
            $this->array2string(array_reverse($issuer)).$serialNumber
        );
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
        if (0 === strpos($ssl['serialNumber'], '0x')) {
            $ssl['serialNumber'] = $this->hex2dec($ssl['serialNumberHex']);
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
