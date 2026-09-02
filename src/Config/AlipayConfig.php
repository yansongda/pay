<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;

class AlipayConfig extends AbstractConfig
{
    private string $appId = '';
    private string $appSecretCert = '';
    private string $appPublicCertPath = '';
    private string $alipayPublicCertPath = '';
    private string $alipayRootCertPath = '';
    private ?string $notifyUrl = null;
    private ?string $returnUrl = null;
    private ?string $appAuthToken = null;
    private ?string $serviceProviderId = null;
    private int $mode = Pay::MODE_NORMAL;
    private string $version = 'v2';
    private ?string $alipayPublicKey = null;

    public function setAppId(string $value): void
    {
        $this->appId = $value;
    }

    public function setAppSecretCert(string $value): void
    {
        $this->appSecretCert = $value;
    }

    public function setAppPublicCertPath(string $value): void
    {
        $this->appPublicCertPath = $value;
    }

    public function setAlipayPublicCertPath(string $value): void
    {
        $this->alipayPublicCertPath = $value;
    }

    public function setAlipayRootCertPath(string $value): void
    {
        $this->alipayRootCertPath = $value;
    }

    public function setNotifyUrl(?string $value): void
    {
        $this->notifyUrl = $value;
    }

    public function setReturnUrl(?string $value): void
    {
        $this->returnUrl = $value;
    }

    public function setAppAuthToken(?string $value): void
    {
        $this->appAuthToken = $value;
    }

    public function setServiceProviderId(?string $value): void
    {
        $this->serviceProviderId = $value;
    }

    public function setMode(int $value): void
    {
        $this->mode = $value;
    }

    public function setVersion(string $value): void
    {
        $this->version = $value;
    }

    public function setAlipayPublicKey(?string $value): void
    {
        $this->alipayPublicKey = $value;
    }

    public function getAppId(): string
    {
        return $this->appId;
    }

    public function getAppSecretCert(): string
    {
        return $this->appSecretCert;
    }

    public function getAppPublicCertPath(): string
    {
        return $this->appPublicCertPath;
    }

    public function getAlipayPublicCertPath(): string
    {
        return $this->alipayPublicCertPath;
    }

    public function getAlipayRootCertPath(): string
    {
        return $this->alipayRootCertPath;
    }

    public function getNotifyUrl(): ?string
    {
        return $this->notifyUrl;
    }

    public function getReturnUrl(): ?string
    {
        return $this->returnUrl;
    }

    /**
     * 第三方应用授权 token.
     */
    public function getAppAuthToken(): ?string
    {
        return $this->appAuthToken;
    }

    /**
     * 服务商模式下的服务商 id.
     */
    public function getServiceProviderId(): ?string
    {
        return $this->serviceProviderId;
    }

    public function getMode(): int
    {
        return $this->mode;
    }

    /**
     * 接口版本：v2（网关签名）/ v3（开放平台 V3 签名）.
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * 支付宝公钥（V3 公钥模式下使用，不含 PEM 头尾的纯 base64 串）.
     */
    public function getAlipayPublicKey(): ?string
    {
        return $this->alipayPublicKey;
    }

    /**
     * @throws InvalidConfigException 缺少必要配置参数
     */
    protected function validateRequired(): void
    {
        switch ($this->version) {
            case 'v2':
                $this->validateNotEmpty(
                    ['appId', 'appSecretCert', 'appPublicCertPath', 'alipayPublicCertPath', 'alipayRootCertPath'],
                    Exception::CONFIG_ALIPAY_INVALID,
                    '配置异常: 缺少支付宝配置'
                );

                break;

            case 'v3':
                if (!empty($this->appPublicCertPath)) {
                    // 证书模式
                    $this->validateNotEmpty(
                        ['appId', 'appSecretCert', 'appPublicCertPath', 'alipayPublicCertPath', 'alipayRootCertPath'],
                        Exception::CONFIG_ALIPAY_INVALID,
                        '配置异常: 缺少支付宝配置'
                    );
                } else {
                    // 公钥模式
                    $this->validateNotEmpty(
                        ['appId', 'appSecretCert', 'alipayPublicKey'],
                        Exception::CONFIG_ALIPAY_INVALID,
                        '配置异常: 缺少支付宝配置'
                    );
                }

                break;

            default:
                throw new InvalidConfigException(
                    Exception::CONFIG_ALIPAY_INVALID,
                    '配置异常: version 仅支持 v2 或 v3，当前为 ['.$this->version.']'
                );
        }
    }
}
