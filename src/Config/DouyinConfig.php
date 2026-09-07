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
    private string $douyinPublicKey = '';
    private ?string $notifyUrl = null;
    private int $mode = Pay::MODE_NORMAL;

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

    public function setDouyinPublicKey(string $value): void
    {
        $this->douyinPublicKey = $value;
    }

    public function setNotifyUrl(?string $value): void
    {
        $this->notifyUrl = $value;
    }

    public function setMode(int $value): void
    {
        $this->mode = $value;
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

    public function getDouyinPublicKey(): string
    {
        return $this->douyinPublicKey;
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
        $this->validateNotEmpty(
            ['appId', 'appSecret'],
            Exception::CONFIG_DOUYIN_INVALID,
            '配置异常: 缺少抖音配置'
        );
    }
}
