<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

class DouyinConfig implements ConfigInterface
{
    public function __construct(
        public string $mini_app_id,
        public string $mch_secret_token,
        public string $mch_secret_salt,
        public string $mch_id = '',
        public string $thirdparty_id = '',
        public string $notify_url = '',
    ) {
    }

    public function toArray(): array
    {
        return [
            'mch_id' => $this->mch_id,
            'mch_secret_token' => $this->mch_secret_token,
            'mch_secret_salt' => $this->mch_secret_salt,
            'mini_app_id' => $this->mini_app_id,
            'thirdparty_id' => $this->thirdparty_id,
            'notify_url' => $this->notify_url,
        ];
    }

    public static function fromArray(array $config): self
    {
        return new self(
            mini_app_id: $config['mini_app_id'] ?? '',
            mch_secret_token: $config['mch_secret_token'] ?? '',
            mch_secret_salt: $config['mch_secret_salt'] ?? '',
            mch_id: $config['mch_id'] ?? '',
            thirdparty_id: $config['thirdparty_id'] ?? '',
            notify_url: $config['notify_url'] ?? '',
        );
    }
}
