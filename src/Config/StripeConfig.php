<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Supports\Config as BaseConfig;

class StripeConfig extends BaseConfig implements ProviderConfigInterface
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

    public function getSecretKey(): string
    {
        return $this->get('secret_key', '');
    }

    public function getWebhookSecret(): ?string
    {
        return $this->get('webhook_secret');
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
     * @throws InvalidConfigException
     */
    private function validateRequired(): void
    {
        $required = ['secret_key'];

        foreach ($required as $key) {
            if (empty($this->get($key))) {
                throw new InvalidConfigException(
                    Exception::CONFIG_STRIPE_INVALID,
                    "配置异常: 缺少 Stripe 配置 -- [{$key}]"
                );
            }
        }
    }
}
