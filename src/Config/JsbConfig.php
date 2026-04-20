<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;

class JsbConfig extends AbstractConfig
{
    private string $svr_code = '';
    private string $partner_id = '';
    private string $public_key_code = '';
    private string $mch_secret_cert_path = '';
    private ?string $mch_public_cert_path = null;
    private string $jsb_public_cert_path = '';
    private ?string $notify_url = null;
    private int $mode = Pay::MODE_NORMAL;

    public function setSvrCode(string $value): void
    {
        $this->svr_code = $value;
    }

    public function setPartnerId(string $value): void
    {
        $this->partner_id = $value;
    }

    public function setPublicKeyCode(string $value): void
    {
        $this->public_key_code = $value;
    }

    public function setMchSecretCertPath(string $value): void
    {
        $this->mch_secret_cert_path = $value;
    }

    public function setMchPublicCertPath(?string $value): void
    {
        $this->mch_public_cert_path = $value;
    }

    public function setJsbPublicCertPath(string $value): void
    {
        $this->jsb_public_cert_path = $value;
    }

    public function setNotifyUrl(?string $value): void
    {
        $this->notify_url = $value;
    }

    public function setMode(int $value): void
    {
        $this->mode = $value;
    }

    public function getSvrCode(): string
    {
        return $this->svr_code;
    }

    public function getPartnerId(): string
    {
        return $this->partner_id;
    }

    public function getPublicKeyCode(): string
    {
        return $this->public_key_code;
    }

    public function getMchSecretCertPath(): string
    {
        return $this->mch_secret_cert_path;
    }

    public function getMchPublicCertPath(): ?string
    {
        return $this->mch_public_cert_path;
    }

    public function getJsbPublicCertPath(): string
    {
        return $this->jsb_public_cert_path;
    }

    public function getNotifyUrl(): ?string
    {
        return $this->notify_url;
    }

    public function getMode(): int
    {
        return $this->mode;
    }

    protected function validateRequired(): void
    {
        $required = ['partner_id', 'public_key_code', 'mch_secret_cert_path', 'jsb_public_cert_path'];

        foreach ($required as $prop) {
            if (empty($this->{$prop})) {
                throw new InvalidConfigException(
                    Exception::CONFIG_JSB_INVALID,
                    "配置异常: 缺少江苏银行配置 -- [{$prop}]"
                );
            }
        }
    }
}
