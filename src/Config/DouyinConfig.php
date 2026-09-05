<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;

class DouyinConfig extends AbstractConfig
{
    private string $appId = '';
    private string $appSecret = '';
    private string $appPrivateKey = '';
    private string $platformPublicKey = '';
    private ?string $refundNotifyUrl = null;
    private ?string $notifyUrl = null;
    private int $mode = Pay::MODE_NORMAL;
    private ?string $_accessToken = null;
    private ?int $_accessTokenExpiry = null;

    public function setAppId(string $value): void
    {
        $this->appId = $value;
    }

    public function setAppSecret(string $value): void
    {
        $this->appSecret = $value;
    }

    public function setAppPrivateKey(string $value): void
    {
        $this->appPrivateKey = $value;
    }

    public function setPlatformPublicKey(string $value): void
    {
        $this->platformPublicKey = $value;
    }

    public function setRefundNotifyUrl(?string $value): void
    {
        $this->refundNotifyUrl = $value;
    }

    public function setNotifyUrl(?string $value): void
    {
        $this->notifyUrl = $value;
    }

    public function setMode(int $value): void
    {
        $this->mode = $value;
    }

    public function setAccessToken(?string $value): void
    {
        $this->_accessToken = $value;
    }

    public function setAccessTokenExpiry(?int $value): void
    {
        $this->_accessTokenExpiry = $value;
    }

    public function getAppId(): string
    {
        return $this->appId;
    }

    public function getAppSecret(): string
    {
        return $this->appSecret;
    }

    public function getAppPrivateKey(): string
    {
        return $this->appPrivateKey;
    }

    public function getPlatformPublicKey(): string
    {
        return $this->platformPublicKey;
    }

    public function getRefundNotifyUrl(): ?string
    {
        return $this->refundNotifyUrl;
    }

    public function getNotifyUrl(): ?string
    {
        return $this->notifyUrl;
    }

    public function getMode(): int
    {
        return $this->mode;
    }

    public function getAccessToken(): ?string
    {
        return $this->_accessToken;
    }

    public function getAccessTokenExpiry(): ?int
    {
        return $this->_accessTokenExpiry;
    }

    /**
     * @throws InvalidConfigException 缺少必要配置参数
     */
    protected function validateRequired(): void
    {
        $this->validateNotEmpty(
            ['appId', 'appSecret'],
            Exception::CONFIG_DOUYIN_INVALID,
            '配置异常: 缺少抖音配置'
        );
    }
}
