<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;

class StripeConfig extends AbstractConfig
{
    private string $secret_key = '';
    private ?string $webhook_secret = null;
    private ?string $notify_url = null;
    private ?string $success_url = null;
    private ?string $cancel_url = null;
    private int $mode = Pay::MODE_NORMAL;

    public function setSecretKey(string $value): void
    {
        $this->secret_key = $value;
    }

    public function setWebhookSecret(?string $value): void
    {
        $this->webhook_secret = $value;
    }

    public function setNotifyUrl(?string $value): void
    {
        $this->notify_url = $value;
    }

    public function setSuccessUrl(?string $value): void
    {
        $this->success_url = $value;
    }

    public function setCancelUrl(?string $value): void
    {
        $this->cancel_url = $value;
    }

    public function setMode(int $value): void
    {
        $this->mode = $value;
    }

    public function getSecretKey(): string
    {
        return $this->secret_key;
    }

    public function getWebhookSecret(): ?string
    {
        return $this->webhook_secret;
    }

    public function getNotifyUrl(): ?string
    {
        return $this->notify_url;
    }

    public function getSuccessUrl(): ?string
    {
        return $this->success_url;
    }

    public function getCancelUrl(): ?string
    {
        return $this->cancel_url;
    }

    public function getMode(): int
    {
        return $this->mode;
    }

    protected function validateRequired(): void
    {
        if (empty($this->secret_key)) {
            throw new InvalidConfigException(
                Exception::CONFIG_STRIPE_INVALID,
                '配置异常: 缺少 Stripe 配置 -- [secret_key]'
            );
        }
    }
}
