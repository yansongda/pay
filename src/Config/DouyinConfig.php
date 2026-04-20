<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;

class DouyinConfig extends AbstractConfig
{
    private ?string $mch_id = null;
    private string $mch_secret_token = '';
    private string $mch_secret_salt = '';
    private string $mini_app_id = '';
    private ?string $thirdparty_id = null;
    private ?string $notify_url = null;
    private int $mode = Pay::MODE_NORMAL;

    public function setMchId(?string $value): void
    {
        $this->mch_id = $value;
    }

    public function setMchSecretToken(string $value): void
    {
        $this->mch_secret_token = $value;
    }

    public function setMchSecretSalt(string $value): void
    {
        $this->mch_secret_salt = $value;
    }

    public function setMiniAppId(string $value): void
    {
        $this->mini_app_id = $value;
    }

    public function setThirdpartyId(?string $value): void
    {
        $this->thirdparty_id = $value;
    }

    public function setNotifyUrl(?string $value): void
    {
        $this->notify_url = $value;
    }

    public function setMode(int $value): void
    {
        $this->mode = $value;
    }

    public function getMchId(): ?string
    {
        return $this->mch_id;
    }

    public function getMchSecretToken(): string
    {
        return $this->mch_secret_token;
    }

    public function getMchSecretSalt(): string
    {
        return $this->mch_secret_salt;
    }

    public function getMiniAppId(): string
    {
        return $this->mini_app_id;
    }

    public function getThirdpartyId(): ?string
    {
        return $this->thirdparty_id;
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
        if (empty($this->mini_app_id)) {
            throw new InvalidConfigException(
                Exception::CONFIG_DOUYIN_INVALID,
                '配置异常: 缺少抖音配置 -- [mini_app_id]'
            );
        }
    }
}
