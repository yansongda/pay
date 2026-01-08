<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

use Yansongda\Pay\Pay;

class UnipayConfig implements ConfigInterface
{
    public function __construct(
        public string $mch_id,
        public string $mch_cert_path,
        public string $mch_cert_password,
        public string $unipay_public_cert_path,
        public string $return_url,
        public string $notify_url,
        public string $mch_secret_key = '',
        public int $mode = Pay::MODE_NORMAL,
    ) {
    }

    public function toArray(): array
    {
        return [
            'mch_id' => $this->mch_id,
            'mch_secret_key' => $this->mch_secret_key,
            'mch_cert_path' => $this->mch_cert_path,
            'mch_cert_password' => $this->mch_cert_password,
            'unipay_public_cert_path' => $this->unipay_public_cert_path,
            'return_url' => $this->return_url,
            'notify_url' => $this->notify_url,
            'mode' => $this->mode,
        ];
    }

    public static function fromArray(array $config): self
    {
        return new self(
            mch_id: $config['mch_id'] ?? '',
            mch_cert_path: $config['mch_cert_path'] ?? '',
            mch_cert_password: $config['mch_cert_password'] ?? '',
            unipay_public_cert_path: $config['unipay_public_cert_path'] ?? '',
            return_url: $config['return_url'] ?? '',
            notify_url: $config['notify_url'] ?? '',
            mch_secret_key: $config['mch_secret_key'] ?? '',
            mode: $config['mode'] ?? Pay::MODE_NORMAL,
        );
    }
}
