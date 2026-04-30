<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;

class PaypalConfig extends AbstractConfig
{
    private string $clientId = '';
    private string $appSecret = '';
    private ?string $webhookId = null;
    private ?string $notifyUrl = null;
    private ?string $returnUrl = null;
    private ?string $cancelUrl = null;
    private ?string $brandName = null;
    private int $mode = Pay::MODE_NORMAL;
    private ?string $_accessToken = null;
    private ?int $_accessTokenExpiry = null;

    public function setClientId(string $value): void
    {
        $this->clientId = $value;
    }

    public function setAppSecret(string $value): void
    {
        $this->appSecret = $value;
    }

    public function setWebhookId(?string $value): void
    {
        $this->webhookId = $value;
    }

    public function setNotifyUrl(?string $value): void
    {
        $this->notifyUrl = $value;
    }

    public function setReturnUrl(?string $value): void
    {
        $this->returnUrl = $value;
    }

    public function setCancelUrl(?string $value): void
    {
        $this->cancelUrl = $value;
    }

    public function setBrandName(?string $value): void
    {
        $this->brandName = $value;
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

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getAppSecret(): string
    {
        return $this->appSecret;
    }

    public function getWebhookId(): ?string
    {
        return $this->webhookId;
    }

    public function getNotifyUrl(): ?string
    {
        return $this->notifyUrl;
    }

    public function getReturnUrl(): ?string
    {
        return $this->returnUrl;
    }

    public function getCancelUrl(): ?string
    {
        return $this->cancelUrl;
    }

    public function getBrandName(): ?string
    {
        return $this->brandName;
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
        $required = ['clientId' => 'client_id', 'appSecret' => 'app_secret'];

        foreach ($required as $prop => $key) {
            if (empty($this->{$prop})) {
                throw new InvalidConfigException(
                    Exception::CONFIG_PAYPAL_INVALID,
                    "配置异常: 缺少 PayPal 配置 -- [{$key}]"
                );
            }
        }
    }
}
