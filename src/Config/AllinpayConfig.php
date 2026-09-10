<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;

class AllinpayConfig extends AbstractConfig
{
    private string $cusid = '';
    private string $appid = '';
    private ?string $orgid = null;
    private string $mchSecretKey = '';
    private string $allinpayPublicKey = '';
    private ?string $notifyUrl = null;
    private int $mode = Pay::MODE_NORMAL;

    public function setCusid(string $value): void
    {
        $this->cusid = $value;
    }

    public function setAppid(string $value): void
    {
        $this->appid = $value;
    }

    public function setOrgid(?string $value): void
    {
        $this->orgid = $value;
    }

    public function setMchSecretKey(string $value): void
    {
        $this->mchSecretKey = $value;
    }

    public function setAllinpayPublicKey(string $value): void
    {
        $this->allinpayPublicKey = $value;
    }

    public function setNotifyUrl(?string $value): void
    {
        $this->notifyUrl = $value;
    }

    public function setMode(int $value): void
    {
        $this->mode = $value;
    }

    public function getCusid(): string
    {
        return $this->cusid;
    }

    public function getAppid(): string
    {
        return $this->appid;
    }

    public function getOrgid(): ?string
    {
        return $this->orgid;
    }

    public function getMchSecretKey(): string
    {
        return $this->mchSecretKey;
    }

    public function getAllinpayPublicKey(): string
    {
        return $this->allinpayPublicKey;
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
     * @throws InvalidConfigException mode 配置值不合法
     */
    protected function supportedModes(): array
    {
        return [Pay::MODE_NORMAL, Pay::MODE_SANDBOX];
    }

    /**
     * @throws InvalidConfigException 缺少必要配置参数
     */
    protected function validateRequired(): void
    {
        $this->validateNotEmpty(
            ['cusid', 'appid', 'mchSecretKey', 'allinpayPublicKey'],
            Exception::CONFIG_ALLINPAY_INVALID,
            '配置异常: 缺少通联支付配置'
        );
    }
}
