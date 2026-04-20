<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;

class UnipayConfig extends AbstractConfig
{
    private string $mch_cert_path = '';
    private string $mch_cert_password = '';
    private ?string $unipay_public_cert_path = null;
    private ?string $mch_secret_key = null;
    private ?string $notify_url = null;
    private ?string $mch_id = null;
    private ?string $return_url = null;
    private array $certs = [];
    private int $mode = Pay::MODE_NORMAL;

    public function setMchCertPath(string $value): void
    {
        $this->mch_cert_path = $value;
    }

    public function setMchCertPassword(string $value): void
    {
        $this->mch_cert_password = $value;
    }

    public function setUnipayPublicCertPath(?string $value): void
    {
        $this->unipay_public_cert_path = $value;
    }

    public function setMchSecretKey(?string $value): void
    {
        $this->mch_secret_key = $value;
    }

    public function setNotifyUrl(?string $value): void
    {
        $this->notify_url = $value;
    }

    public function setMchId(?string $value): void
    {
        $this->mch_id = $value;
    }

    public function setReturnUrl(?string $value): void
    {
        $this->return_url = $value;
    }

    public function setCerts(array $value): void
    {
        $this->certs = $value;
    }

    public function setMode(int $value): void
    {
        $this->mode = $value;
    }

    public function getMchCertPath(): string
    {
        return $this->mch_cert_path;
    }

    public function getMchCertPassword(): string
    {
        return $this->mch_cert_password;
    }

    public function getUnipayPublicCertPath(): ?string
    {
        return $this->unipay_public_cert_path;
    }

    public function getMchSecretKey(): ?string
    {
        return $this->mch_secret_key;
    }

    public function getNotifyUrl(): ?string
    {
        return $this->notify_url;
    }

    public function getMchId(): ?string
    {
        return $this->mch_id;
    }

    public function getReturnUrl(): ?string
    {
        return $this->return_url;
    }

    public function getCerts(): array
    {
        return $this->certs;
    }

    public function getMode(): int
    {
        return $this->mode;
    }

    protected function validateRequired(): void
    {
        if (!empty($this->mch_secret_key)) {
            return;
        }

        $required = ['mch_cert_path', 'mch_cert_password'];

        foreach ($required as $prop) {
            if (empty($this->{$prop})) {
                throw new InvalidConfigException(
                    Exception::CONFIG_UNIPAY_INVALID,
                    "配置异常: 缺少银联配置 -- [{$prop}]"
                );
            }
        }
    }
}
