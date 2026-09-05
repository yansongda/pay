<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\Exception;

/**
 * 支付宝 V2 配置：网关签名（form 表单，`gateway.do`）.
 */
class AlipayV2Config extends AlipayConfig
{
    private string $appPublicCertPath = '';
    private string $alipayPublicCertPath = '';
    private string $alipayRootCertPath = '';
    private string $version = self::VERSION_V2;

    public function setAppPublicCertPath(string $value): void
    {
        $this->appPublicCertPath = $value;
    }

    public function setAlipayPublicCertPath(string $value): void
    {
        $this->alipayPublicCertPath = $value;
    }

    public function setAlipayRootCertPath(string $value): void
    {
        $this->alipayRootCertPath = $value;
    }

    public function getAppPublicCertPath(): string
    {
        return $this->appPublicCertPath;
    }

    public function getAlipayPublicCertPath(): string
    {
        return $this->alipayPublicCertPath;
    }

    public function getAlipayRootCertPath(): string
    {
        return $this->alipayRootCertPath;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @throws InvalidConfigException 缺少必要配置参数
     */
    protected function validateRequired(): void
    {
        $this->validateNotEmpty(
            ['appId', 'appSecretCert', 'appPublicCertPath', 'alipayPublicCertPath', 'alipayRootCertPath'],
            Exception::CONFIG_ALIPAY_INVALID,
            '配置异常: 缺少支付宝配置'
        );
    }
}
