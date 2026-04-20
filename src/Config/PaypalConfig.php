<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;

class PaypalConfig extends AbstractConfig
{
    private string $client_id = '';
    private string $app_secret = '';
    private ?string $webhook_id = null;
    private ?string $notify_url = null;
    private ?string $return_url = null;
    private ?string $cancel_url = null;
    private ?string $brand_name = null;
    private int $mode = Pay::MODE_NORMAL;
    private ?string $_access_token = null;
    private ?int $_access_token_expiry = null;

    public function setClientId(string $value): void
    {
        $this->client_id = $value;
    }

    public function setAppSecret(string $value): void
    {
        $this->app_secret = $value;
    }

    public function setWebhookId(?string $value): void
    {
        $this->webhook_id = $value;
    }

    public function setNotifyUrl(?string $value): void
    {
        $this->notify_url = $value;
    }

    public function setReturnUrl(?string $value): void
    {
        $this->return_url = $value;
    }

    public function setCancelUrl(?string $value): void
    {
        $this->cancel_url = $value;
    }

    public function setBrandName(?string $value): void
    {
        $this->brand_name = $value;
    }

    public function setMode(int $value): void
    {
        $this->mode = $value;
    }

    public function setAccessToken(?string $value): void
    {
        $this->_access_token = $value;
    }

    public function setAccessTokenExpiry(?int $value): void
    {
        $this->_access_token_expiry = $value;
    }

    public function getClientId(): string
    {
        return $this->client_id;
    }

    public function getAppSecret(): string
    {
        return $this->app_secret;
    }

    public function getWebhookId(): ?string
    {
        return $this->webhook_id;
    }

    public function getNotifyUrl(): ?string
    {
        return $this->notify_url;
    }

    public function getReturnUrl(): ?string
    {
        return $this->return_url;
    }

    public function getCancelUrl(): ?string
    {
        return $this->cancel_url;
    }

    public function getBrandName(): ?string
    {
        return $this->brand_name;
    }

    public function getMode(): int
    {
        return $this->mode;
    }

    public function getAccessToken(): ?string
    {
        return $this->_access_token;
    }

    public function getAccessTokenExpiry(): ?int
    {
        return $this->_access_token_expiry;
    }

    protected function validateRequired(): void
    {
        $required = ['client_id', 'app_secret'];

        foreach ($required as $prop) {
            if (empty($this->{$prop})) {
                throw new InvalidConfigException(
                    Exception::CONFIG_PAYPAL_INVALID,
                    "配置异常: 缺少 PayPal 配置 -- [{$prop}]"
                );
            }
        }
    }
}
