<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;

class UnipayConfig extends AbstractConfig
{
    private string $mchCertPath = '';
    private string $mchCertPassword = '';
    private ?string $unipayPublicCertPath = null;
    private ?string $mchSecretKey = null;
    private ?string $notifyUrl = null;
    private ?string $mchId = null;
    private ?string $returnUrl = null;
    private int $mode = Pay::MODE_NORMAL;

    public function setMchCertPath(string $value): void
    {
        $this->mchCertPath = $value;
    }

    public function setMchCertPassword(string $value): void
    {
        $this->mchCertPassword = $value;
    }

    public function setUnipayPublicCertPath(?string $value): void
    {
        $this->unipayPublicCertPath = $value;
    }

    public function setMchSecretKey(?string $value): void
    {
        $this->mchSecretKey = $value;
    }

    public function setNotifyUrl(?string $value): void
    {
        $this->notifyUrl = $value;
    }

    public function setMchId(?string $value): void
    {
        $this->mchId = $value;
    }

    public function setReturnUrl(?string $value): void
    {
        $this->returnUrl = $value;
    }

    public function setMode(int $value): void
    {
        $this->mode = $value;
    }

    public function getMchCertPath(): string
    {
        return $this->mchCertPath;
    }

    public function getMchCertPassword(): string
    {
        return $this->mchCertPassword;
    }

    public function getUnipayPublicCertPath(): ?string
    {
        return $this->unipayPublicCertPath;
    }

    public function getMchSecretKey(): ?string
    {
        return $this->mchSecretKey;
    }

    public function getNotifyUrl(): ?string
    {
        return $this->notifyUrl;
    }

    public function getMchId(): ?string
    {
        return $this->mchId;
    }

    public function getReturnUrl(): ?string
    {
        return $this->returnUrl;
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
        if (!empty($this->mchSecretKey)) {
            return;
        }

        $required = ['mchCertPath' => 'mch_cert_path', 'mchCertPassword' => 'mch_cert_password'];

        foreach ($required as $prop => $key) {
            if (empty($this->{$prop})) {
                throw new InvalidConfigException(
                    Exception::CONFIG_UNIPAY_INVALID,
                    "配置异常: 缺少银联配置 -- [{$key}]"
                );
            }
        }
    }
}
