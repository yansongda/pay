<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

use Yansongda\Pay\Pay;

class WechatConfig implements ConfigInterface
{
    public function __construct(
        public string $mch_id,
        public string $mch_secret_key,
        public string $mch_secret_cert,
        public string $mch_public_cert_path,
        public string $notify_url,
        public string $mch_secret_key_v2 = '',
        public string $mp_app_id = '',
        public string $mini_app_id = '',
        public string $app_id = '',
        public string $sub_mp_app_id = '',
        public string $sub_app_id = '',
        public string $sub_mini_app_id = '',
        public string $sub_mch_id = '',
        public array $wechat_public_cert_path = [],
        public int $mode = Pay::MODE_NORMAL,
    ) {
    }

    public function toArray(): array
    {
        return [
            'mch_id' => $this->mch_id,
            'mch_secret_key_v2' => $this->mch_secret_key_v2,
            'mch_secret_key' => $this->mch_secret_key,
            'mch_secret_cert' => $this->mch_secret_cert,
            'mch_public_cert_path' => $this->mch_public_cert_path,
            'notify_url' => $this->notify_url,
            'mp_app_id' => $this->mp_app_id,
            'mini_app_id' => $this->mini_app_id,
            'app_id' => $this->app_id,
            'sub_mp_app_id' => $this->sub_mp_app_id,
            'sub_app_id' => $this->sub_app_id,
            'sub_mini_app_id' => $this->sub_mini_app_id,
            'sub_mch_id' => $this->sub_mch_id,
            'wechat_public_cert_path' => $this->wechat_public_cert_path,
            'mode' => $this->mode,
        ];
    }

    public static function fromArray(array $config): self
    {
        return new self(
            mch_id: $config['mch_id'] ?? '',
            mch_secret_key: $config['mch_secret_key'] ?? '',
            mch_secret_cert: $config['mch_secret_cert'] ?? '',
            mch_public_cert_path: $config['mch_public_cert_path'] ?? '',
            notify_url: $config['notify_url'] ?? '',
            mch_secret_key_v2: $config['mch_secret_key_v2'] ?? '',
            mp_app_id: $config['mp_app_id'] ?? '',
            mini_app_id: $config['mini_app_id'] ?? '',
            app_id: $config['app_id'] ?? '',
            sub_mp_app_id: $config['sub_mp_app_id'] ?? '',
            sub_app_id: $config['sub_app_id'] ?? '',
            sub_mini_app_id: $config['sub_mini_app_id'] ?? '',
            sub_mch_id: $config['sub_mch_id'] ?? '',
            wechat_public_cert_path: $config['wechat_public_cert_path'] ?? [],
            mode: $config['mode'] ?? Pay::MODE_NORMAL,
        );
    }
}
