<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

use Yansongda\Pay\Pay;

class JsbConfig implements ConfigInterface
{
    public function __construct(
        public string $partner_id,
        public string $mch_secret_cert_path,
        public string $mch_public_cert_path,
        public string $jsb_public_cert_path,
        public string $svr_code = '',
        public string $public_key_code = '00',
        public string $notify_url = '',
        public int $mode = Pay::MODE_NORMAL,
    ) {
    }

    public function toArray(): array
    {
        return [
            'svr_code' => $this->svr_code,
            'partner_id' => $this->partner_id,
            'public_key_code' => $this->public_key_code,
            'mch_secret_cert_path' => $this->mch_secret_cert_path,
            'mch_public_cert_path' => $this->mch_public_cert_path,
            'jsb_public_cert_path' => $this->jsb_public_cert_path,
            'notify_url' => $this->notify_url,
            'mode' => $this->mode,
        ];
    }

    public static function fromArray(array $config): self
    {
        return new self(
            partner_id: $config['partner_id'] ?? '',
            mch_secret_cert_path: $config['mch_secret_cert_path'] ?? '',
            mch_public_cert_path: $config['mch_public_cert_path'] ?? '',
            jsb_public_cert_path: $config['jsb_public_cert_path'] ?? '',
            svr_code: $config['svr_code'] ?? '',
            public_key_code: $config['public_key_code'] ?? '00',
            notify_url: $config['notify_url'] ?? '',
            mode: $config['mode'] ?? Pay::MODE_NORMAL,
        );
    }
}
