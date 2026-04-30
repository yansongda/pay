<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;

class DouyinConfig extends AbstractConfig
{
    private ?string $mchId = null;
    private string $mchSecretToken = '';
    private string $mchSecretSalt = '';
    private string $miniAppId = '';
    private ?string $thirdpartyId = null;
    private ?string $notifyUrl = null;
    private int $mode = Pay::MODE_NORMAL;

    public function setMchId(?string $value): void
    {
        $this->mchId = $value;
    }

    public function setMchSecretToken(string $value): void
    {
        $this->mchSecretToken = $value;
    }

    public function setMchSecretSalt(string $value): void
    {
        $this->mchSecretSalt = $value;
    }

    public function setMiniAppId(string $value): void
    {
        $this->miniAppId = $value;
    }

    public function setThirdpartyId(?string $value): void
    {
        $this->thirdpartyId = $value;
    }

    public function setNotifyUrl(?string $value): void
    {
        $this->notifyUrl = $value;
    }

    public function setMode(int $value): void
    {
        $this->mode = $value;
    }

    public function getMchId(): ?string
    {
        return $this->mchId;
    }

    public function getMchSecretToken(): string
    {
        return $this->mchSecretToken;
    }

    public function getMchSecretSalt(): string
    {
        return $this->mchSecretSalt;
    }

    public function getMiniAppId(): string
    {
        return $this->miniAppId;
    }

    public function getThirdpartyId(): ?string
    {
        return $this->thirdpartyId;
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
        if (empty($this->miniAppId)) {
            throw new InvalidConfigException(
                Exception::CONFIG_DOUYIN_INVALID,
                '配置异常: 缺少抖音配置 -- [mini_app_id]'
            );
        }
    }
}
