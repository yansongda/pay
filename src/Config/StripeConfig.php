<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;

class StripeConfig extends AbstractConfig
{
    private string $secretKey = '';
    private ?string $webhookSecret = null;
    private ?string $notifyUrl = null;
    private ?string $successUrl = null;
    private ?string $cancelUrl = null;
    private int $mode = Pay::MODE_NORMAL;

    public function setSecretKey(string $value): void
    {
        $this->secretKey = $value;
    }

    public function setWebhookSecret(?string $value): void
    {
        $this->webhookSecret = $value;
    }

    public function setNotifyUrl(?string $value): void
    {
        $this->notifyUrl = $value;
    }

    public function setSuccessUrl(?string $value): void
    {
        $this->successUrl = $value;
    }

    public function setCancelUrl(?string $value): void
    {
        $this->cancelUrl = $value;
    }

    public function setMode(int $value): void
    {
        $this->mode = $value;
    }

    public function getSecretKey(): string
    {
        return $this->secretKey;
    }

    public function getWebhookSecret(): ?string
    {
        return $this->webhookSecret;
    }

    public function getNotifyUrl(): ?string
    {
        return $this->notifyUrl;
    }

    public function getSuccessUrl(): ?string
    {
        return $this->successUrl;
    }

    public function getCancelUrl(): ?string
    {
        return $this->cancelUrl;
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
        if (empty($this->secretKey)) {
            throw new InvalidConfigException(
                Exception::CONFIG_STRIPE_INVALID,
                '配置异常: 缺少 Stripe 配置 -- [secret_key]'
            );
        }
    }
}
