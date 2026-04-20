<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Supports\Config as BaseConfig;

class PaypalConfig extends BaseConfig implements ProviderConfigInterface
{
    private string $tenant;

    /**
     * @throws InvalidConfigException
     */
    public function __construct(array $values, string $tenant = 'default')
    {
        parent::__construct($values);

        $this->tenant = $tenant;

        $this->validateRequired();
    }

    public function getTenant(): string
    {
        return $this->tenant;
    }

    public function getClientId(): string
    {
        return $this->get('client_id', '');
    }

    public function getAppSecret(): string
    {
        return $this->get('app_secret', '');
    }

    public function getWebhookId(): ?string
    {
        return $this->get('webhook_id');
    }

    public function getNotifyUrl(): ?string
    {
        return $this->get('notify_url');
    }

    /**
     * 默认返回 MODE_NORMAL.
     */
    public function getMode(): int
    {
        return $this->get('mode', Pay::MODE_NORMAL);
    }

    /**
     * 获取动态存储的 access_token.
     */
    public function getAccessToken(): ?string
    {
        return $this->get('_access_token');
    }

    /**
     * 获取动态存储的 access_token 过期时间.
     */
    public function getAccessTokenExpiry(): ?int
    {
        return $this->get('_access_token_expiry');
    }

    /**
     * @throws InvalidConfigException
     */
    private function validateRequired(): void
    {
        $required = ['client_id', 'app_secret'];

        foreach ($required as $key) {
            if (empty($this->get($key))) {
                throw new InvalidConfigException(
                    Exception::CONFIG_PAYPAL_INVALID,
                    "配置异常: 缺少 PayPal 配置 -- [{$key}]"
                );
            }
        }
    }
}
