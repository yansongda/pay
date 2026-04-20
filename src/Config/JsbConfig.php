<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Supports\Config as BaseConfig;

class JsbConfig extends BaseConfig implements ProviderConfigInterface
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

    public function getSvrCode(): string
    {
        return $this->get('svr_code', '');
    }

    public function getPartnerId(): string
    {
        return $this->get('partner_id', '');
    }

    public function getPublicKeyCode(): string
    {
        return $this->get('public_key_code', '');
    }

    public function getMchSecretCertPath(): string
    {
        return $this->get('mch_secret_cert_path', '');
    }

    public function getMchPublicCertPath(): ?string
    {
        return $this->get('mch_public_cert_path');
    }

    public function getJsbPublicCertPath(): string
    {
        return $this->get('jsb_public_cert_path', '');
    }

    public function getNotifyUrl(): ?string
    {
        return $this->get('notify_url');
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
        $required = ['partner_id', 'public_key_code', 'mch_secret_cert_path', 'jsb_public_cert_path'];

        foreach ($required as $key) {
            if (empty($this->get($key))) {
                throw new InvalidConfigException(
                    Exception::CONFIG_JSB_INVALID,
                    "配置异常: 缺少江苏银行配置 -- [{$key}]"
                );
            }
        }
    }
}
