<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\Exception;

/**
 * 支付宝 V3 配置：OpenAPI（RESTful `/v3/` 路径 + HTTP 头签名）.
 */
class AlipayV3Config extends AlipayConfig
{
    private ?string $alipayPublicKey = null;
    private string $appPublicCertPath = '';
    private string $alipayPublicCertPath = '';
    private string $version = self::VERSION_V3;

    public function setAlipayPublicKey(?string $value): void
    {
        $this->alipayPublicKey = $value;
    }

    public function setAppPublicCertPath(string $value): void
    {
        $this->appPublicCertPath = $value;
    }

    public function setAlipayPublicCertPath(string $value): void
    {
        $this->alipayPublicCertPath = $value;
    }

    /**
     * 支付宝公钥（V3 公钥模式下使用，不含 PEM 头尾的纯 base64 串）.
     */
    public function getAlipayPublicKey(): ?string
    {
        return $this->alipayPublicKey;
    }

    public function getAppPublicCertPath(): string
    {
        return $this->appPublicCertPath;
    }

    public function getAlipayPublicCertPath(): string
    {
        return $this->alipayPublicCertPath;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * 配置了 `app_public_cert_path` 即按证书模式校验，否则按公钥模式校验.
     *
     * @throws InvalidConfigException 缺少必要配置参数
     */
    protected function validateRequired(): void
    {
        if (!empty($this->appPublicCertPath)) {
            // 证书模式（V3 协议无 root-cert-sn，无需支付宝根证书）
            $this->validateNotEmpty(
                ['appId', 'appSecretCert', 'appPublicCertPath', 'alipayPublicCertPath'],
                Exception::CONFIG_ALIPAY_INVALID,
                '配置异常: 缺少支付宝配置'
            );

            return;
        }

        // 公钥模式
        $this->validateNotEmpty(
            ['appId', 'appSecretCert', 'alipayPublicKey'],
            Exception::CONFIG_ALIPAY_INVALID,
            '配置异常: 缺少支付宝配置'
        );
    }
}
