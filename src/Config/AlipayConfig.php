<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;

class AlipayConfig extends AbstractConfig
{
    private string $app_id = '';
    private string $app_secret_cert = '';
    private string $app_public_cert_path = '';
    private string $alipay_public_cert_path = '';
    private string $alipay_root_cert_path = '';
    private ?string $notify_url = null;
    private ?string $return_url = null;
    private ?string $app_auth_token = null;
    private ?string $app_public_cert_sn = null;
    private ?string $alipay_root_cert_sn = null;
    private ?string $service_provider_id = null;
    private int $mode = Pay::MODE_NORMAL;

    public function setAppId(string $value): void
    {
        $this->app_id = $value;
    }

    public function setAppSecretCert(string $value): void
    {
        $this->app_secret_cert = $value;
    }

    public function setAppPublicCertPath(string $value): void
    {
        $this->app_public_cert_path = $value;
    }

    public function setAlipayPublicCertPath(string $value): void
    {
        $this->alipay_public_cert_path = $value;
    }

    public function setAlipayRootCertPath(string $value): void
    {
        $this->alipay_root_cert_path = $value;
    }

    public function setNotifyUrl(?string $value): void
    {
        $this->notify_url = $value;
    }

    public function setReturnUrl(?string $value): void
    {
        $this->return_url = $value;
    }

    public function setAppAuthToken(?string $value): void
    {
        $this->app_auth_token = $value;
    }

    public function setAppPublicCertSn(?string $value): void
    {
        $this->app_public_cert_sn = $value;
    }

    public function setAlipayRootCertSn(?string $value): void
    {
        $this->alipay_root_cert_sn = $value;
    }

    public function setServiceProviderId(?string $value): void
    {
        $this->service_provider_id = $value;
    }

    public function setMode(int $value): void
    {
        $this->mode = $value;
    }

    public function getAppId(): string
    {
        return $this->app_id;
    }

    public function getAppSecretCert(): string
    {
        return $this->app_secret_cert;
    }

    public function getAppPublicCertPath(): string
    {
        return $this->app_public_cert_path;
    }

    public function getAlipayPublicCertPath(): string
    {
        return $this->alipay_public_cert_path;
    }

    public function getAlipayRootCertPath(): string
    {
        return $this->alipay_root_cert_path;
    }

    public function getNotifyUrl(): ?string
    {
        return $this->notify_url;
    }

    public function getReturnUrl(): ?string
    {
        return $this->return_url;
    }

    /**
     * 第三方应用授权 token.
     */
    public function getAppAuthToken(): ?string
    {
        return $this->app_auth_token;
    }

    /**
     * 应用公钥证书序列号（缓存值）.
     */
    public function getAppPublicCertSn(): ?string
    {
        return $this->app_public_cert_sn;
    }

    /**
     * 支付宝根证书序列号（缓存值）.
     */
    public function getAlipayRootCertSn(): ?string
    {
        return $this->alipay_root_cert_sn;
    }

    /**
     * 服务商模式下的服务商 id.
     */
    public function getServiceProviderId(): ?string
    {
        return $this->service_provider_id;
    }

    public function getMode(): int
    {
        return $this->mode;
    }

    protected function validateRequired(): void
    {
        $required = ['app_id', 'app_secret_cert', 'app_public_cert_path', 'alipay_public_cert_path', 'alipay_root_cert_path'];

        foreach ($required as $prop) {
            if (empty($this->{$prop})) {
                throw new InvalidConfigException(
                    Exception::CONFIG_ALIPAY_INVALID,
                    "配置异常: 缺少支付宝配置 -- [{$prop}]"
                );
            }
        }
    }
}
