<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Supports\Config as BaseConfig;

class AlipayConfig extends BaseConfig implements ProviderConfigInterface
{
    private string $tenant;

    /**
     * @throws InvalidConfigException
     */
    public function __construct(array $values, string $tenant = 'default')
    {
        parent::__construct($values);

        $this->tenant = $tenant;

        $this->validateRequired();
    }

    public function getTenant(): string
    {
        return $this->tenant;
    }

    public function getAppId(): string
    {
        return $this->get('app_id', '');
    }

    public function getAppSecretCert(): string
    {
        return $this->get('app_secret_cert', '');
    }

    public function getAppPublicCertPath(): string
    {
        return $this->get('app_public_cert_path', '');
    }

    public function getAlipayPublicCertPath(): string
    {
        return $this->get('alipay_public_cert_path', '');
    }

    public function getAlipayRootCertPath(): string
    {
        return $this->get('alipay_root_cert_path', '');
    }

    public function getNotifyUrl(): ?string
    {
        return $this->get('notify_url');
    }

    public function getReturnUrl(): ?string
    {
        return $this->get('return_url');
    }

    /**
     * 第三方应用授权 token.
     */
    public function getAppAuthToken(): ?string
    {
        return $this->get('app_auth_token');
    }

    /**
     * 应用公钥证书序列号（缓存值）.
     */
    public function getAppPublicCertSn(): ?string
    {
        return $this->get('app_public_cert_sn');
    }

    /**
     * 支付宝根证书序列号（缓存值）.
     */
    public function getAlipayRootCertSn(): ?string
    {
        return $this->get('alipay_root_cert_sn');
    }

    /**
     * 服务商模式下的服务商 id.
     */
    public function getServiceProviderId(): ?string
    {
        return $this->get('service_provider_id');
    }

    /**
     * 默认返回 MODE_NORMAL.
     */
    public function getMode(): int
    {
        return $this->get('mode', Pay::MODE_NORMAL);
    }

    /**
     * 设置应用公钥证书序列号.
     */
    public function setAppPublicCertSn(string $sn): void
    {
        $this->set('app_public_cert_sn', $sn);
    }

    /**
     * 设置支付宝根证书序列号.
     */
    public function setAlipayRootCertSn(string $sn): void
    {
        $this->set('alipay_root_cert_sn', $sn);
    }

    /**
     * @throws InvalidConfigException
     */
    private function validateRequired(): void
    {
        $required = ['app_id', 'app_secret_cert', 'app_public_cert_path', 'alipay_public_cert_path', 'alipay_root_cert_path'];

        foreach ($required as $key) {
            if (empty($this->get($key))) {
                throw new InvalidConfigException(
                    Exception::CONFIG_ALIPAY_INVALID,
                    "配置异常: 缺少支付宝配置 -- [{$key}]"
                );
            }
        }
    }
}
