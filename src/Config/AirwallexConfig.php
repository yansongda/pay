<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;

class AirwallexConfig extends AbstractConfig
{
    private string $clientId = '';
    private string $apiKey = '';
    private ?string $webhookSecret = null;
    private ?string $returnUrl = null;
    private ?string $apiVersion = null;
    private ?string $onBehalfOf = null;
    private ?string $accessToken = null;
    private ?int $accessTokenExpiry = null;
    private int $mode = Pay::MODE_NORMAL;

    public function setClientId(string $value): void
    {
        $this->clientId = $value;
    }

    public function setApiKey(string $value): void
    {
        $this->apiKey = $value;
    }

    public function setWebhookSecret(?string $value): void
    {
        $this->webhookSecret = $value;
    }

    public function setReturnUrl(?string $value): void
    {
        $this->returnUrl = $value;
    }

    public function setApiVersion(?string $value): void
    {
        $this->apiVersion = $value;
    }

    public function setOnBehalfOf(?string $value): void
    {
        $this->onBehalfOf = $value;
    }

    public function setAccessToken(?string $value): void
    {
        $this->accessToken = $value;
    }

    public function setAccessTokenExpiry(int|string|null $value): void
    {
        $this->accessTokenExpiry = null === $value ? null : (int) $value;
    }

    public function setMode(int $value): void
    {
        $this->mode = $value;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getWebhookSecret(): ?string
    {
        return $this->webhookSecret;
    }

    public function getReturnUrl(): ?string
    {
        return $this->returnUrl;
    }

    public function getApiVersion(): ?string
    {
        return $this->apiVersion;
    }

    public function getOnBehalfOf(): ?string
    {
        return $this->onBehalfOf;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function getAccessTokenExpiry(): ?int
    {
        return $this->accessTokenExpiry;
    }

    public function getMode(): int
    {
        return $this->mode;
    }

    /**
     * @throws InvalidConfigException
     */
    protected function validateRequired(): void
    {
        $required = [
            'clientId' => 'client_id',
            'apiKey' => 'api_key',
        ];

        foreach ($required as $prop => $key) {
            if (empty($this->{$prop})) {
                throw new InvalidConfigException(
                    Exception::CONFIG_AIRWALLEX_INVALID,
                    "配置错误: Airwallex 配置缺少 [{$key}]"
                );
            }
        }
    }
}
