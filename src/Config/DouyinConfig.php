<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Supports\Config as BaseConfig;

class DouyinConfig extends BaseConfig implements ProviderConfigInterface
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

    public function getMchId(): ?string
    {
        return $this->get('mch_id');
    }

    public function getMchSecretToken(): string
    {
        return $this->get('mch_secret_token', '');
    }

    public function getMchSecretSalt(): string
    {
        return $this->get('mch_secret_salt', '');
    }

    public function getMiniAppId(): string
    {
        return $this->get('mini_app_id', '');
    }

    public function getThirdpartyId(): ?string
    {
        return $this->get('thirdparty_id');
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
        // 只验证 mini_app_id，其他字段在 Plugin 中检查
        if (empty($this->get('mini_app_id'))) {
            throw new InvalidConfigException(
                Exception::CONFIG_DOUYIN_INVALID,
                '配置异常: 缺少抖音配置 -- [mini_app_id]'
            );
        }
    }
}
