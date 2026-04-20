<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Supports\Config as BaseConfig;

class UnipayConfig extends BaseConfig implements ProviderConfigInterface
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

    public function getMchCertPath(): string
    {
        return $this->get('mch_cert_path', '');
    }

    public function getMchCertPassword(): string
    {
        return $this->get('mch_cert_password', '');
    }

    public function getUnipayPublicCertPath(): ?string
    {
        return $this->get('unipay_public_cert_path');
    }

    public function getMchSecretKey(): ?string
    {
        return $this->get('mch_secret_key');
    }

    public function getNotifyUrl(): ?string
    {
        return $this->get('notify_url');
    }

    /**
     * 商户号.
     */
    public function getMchId(): ?string
    {
        return $this->get('mch_id');
    }

    /**
     * 前台回调地址.
     */
    public function getReturnUrl(): ?string
    {
        return $this->get('return_url');
    }

    /**
     * 证书配置数组.
     *
     * @return array<string, string>
     */
    public function getCerts(): array
    {
        return $this->get('certs', []);
    }

    /**
     * 默认返回 MODE_NORMAL.
     */
    public function getMode(): int
    {
        return $this->get('mode', Pay::MODE_NORMAL);
    }

    /**
     * @throws InvalidConfigException
     */
    private function validateRequired(): void
    {
        $required = ['mch_cert_path', 'mch_cert_password'];

        foreach ($required as $key) {
            if (empty($this->get($key))) {
                throw new InvalidConfigException(
                    Exception::CONFIG_UNIPAY_INVALID,
                    "配置异常: 缺少银联配置 -- [{$key}]"
                );
            }
        }
    }
}
