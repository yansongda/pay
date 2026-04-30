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
     * @throws InvalidConfigException 缺少必要配置参数
     */
    protected function validateRequired(): void
    {
        $required = [
            'appId' => 'app_id',
            'appSecretCert' => 'app_secret_cert',
            'appPublicCertPath' => 'app_public_cert_path',
            'alipayPublicCertPath' => 'alipay_public_cert_path',
            'alipayRootCertPath' => 'alipay_root_cert_path',
        ];

        foreach ($required as $prop => $key) {
            if (empty($this->{$prop})) {
                throw new InvalidConfigException(
                    Exception::CONFIG_ALIPAY_INVALID,
                    "配置异常: 缺少支付宝配置 -- [{$key}]"
                );
            }
        }
    }
}
