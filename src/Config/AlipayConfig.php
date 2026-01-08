<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

use Yansongda\Pay\Pay;

class AlipayConfig implements ConfigInterface
{
    public function __construct(
        public string $app_id,
        public string $app_secret_cert,
        public string $app_public_cert_path,
        public string $alipay_public_cert_path,
        public string $alipay_root_cert_path,
        public string $return_url = '',
        public string $notify_url = '',
        public string $app_auth_token = '',
        public string $service_provider_id = '',
        public int $mode = Pay::MODE_NORMAL,
    ) {
    }

    public function toArray(): array
    {
        return [
            'app_id' => $this->app_id,
            'app_secret_cert' => $this->app_secret_cert,
            'app_public_cert_path' => $this->app_public_cert_path,
            'alipay_public_cert_path' => $this->alipay_public_cert_path,
            'alipay_root_cert_path' => $this->alipay_root_cert_path,
            'return_url' => $this->return_url,
            'notify_url' => $this->notify_url,
            'app_auth_token' => $this->app_auth_token,
            'service_provider_id' => $this->service_provider_id,
            'mode' => $this->mode,
        ];
    }

    public static function fromArray(array $config): self
    {
        return new self(
            app_id: $config['app_id'] ?? '',
            app_secret_cert: $config['app_secret_cert'] ?? '',
            app_public_cert_path: $config['app_public_cert_path'] ?? '',
            alipay_public_cert_path: $config['alipay_public_cert_path'] ?? '',
            alipay_root_cert_path: $config['alipay_root_cert_path'] ?? '',
            return_url: $config['return_url'] ?? '',
            notify_url: $config['notify_url'] ?? '',
            app_auth_token: $config['app_auth_token'] ?? '',
            service_provider_id: $config['service_provider_id'] ?? '',
            mode: $config['mode'] ?? Pay::MODE_NORMAL,
        );
    }
}
