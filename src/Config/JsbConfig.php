<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;

class JsbConfig extends AbstractConfig
{
    private string $svrCode = '';
    private string $partnerId = '';
    private string $publicKeyCode = '';
    private string $mchSecretCertPath = '';
    private ?string $mchPublicCertPath = null;
    private string $jsbPublicCertPath = '';
    private ?string $notifyUrl = null;
    private int $mode = Pay::MODE_NORMAL;

    public function setSvrCode(string $value): void
    {
        $this->svrCode = $value;
    }

    public function setPartnerId(string $value): void
    {
        $this->partnerId = $value;
    }

    public function setPublicKeyCode(string $value): void
    {
        $this->publicKeyCode = $value;
    }

    public function setMchSecretCertPath(string $value): void
    {
        $this->mchSecretCertPath = $value;
    }

    public function setMchPublicCertPath(?string $value): void
    {
        $this->mchPublicCertPath = $value;
    }

    public function setJsbPublicCertPath(string $value): void
    {
        $this->jsbPublicCertPath = $value;
    }

    public function setNotifyUrl(?string $value): void
    {
        $this->notifyUrl = $value;
    }

    public function setMode(int $value): void
    {
        $this->mode = $value;
    }

    public function getSvrCode(): string
    {
        return $this->svrCode;
    }

    public function getPartnerId(): string
    {
        return $this->partnerId;
    }

    public function getPublicKeyCode(): string
    {
        return $this->publicKeyCode;
    }

    public function getMchSecretCertPath(): string
    {
        return $this->mchSecretCertPath;
    }

    public function getMchPublicCertPath(): ?string
    {
        return $this->mchPublicCertPath;
    }

    public function getJsbPublicCertPath(): string
    {
        return $this->jsbPublicCertPath;
    }

    public function getNotifyUrl(): ?string
    {
        return $this->notifyUrl;
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
            'partnerId' => 'partner_id',
            'publicKeyCode' => 'public_key_code',
            'mchSecretCertPath' => 'mch_secret_cert_path',
            'jsbPublicCertPath' => 'jsb_public_cert_path',
        ];

        foreach ($required as $prop => $key) {
            if (empty($this->{$prop})) {
                throw new InvalidConfigException(
                    Exception::CONFIG_JSB_INVALID,
                    "配置异常: 缺少江苏银行配置 -- [{$key}]"
                );
            }
        }
    }
}
