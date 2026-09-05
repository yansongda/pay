<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;

/**
 * 支付宝租户配置（V2 网关签名与 V3 OpenAPI 共用一套配置）.
 */
class AlipayConfig extends AbstractConfig
{
    // ── 公共 ──

    protected string $appId = '';
    protected string $appSecretCert = '';
    protected ?string $notifyUrl = null;
    protected ?string $returnUrl = null;
    protected ?string $appAuthToken = null;
    protected ?string $serviceProviderId = null;
    protected int $mode = Pay::MODE_NORMAL;

    // ── 证书（V2/V3 完全共用）──

    protected string $appPublicCertPath = '';
    protected string $alipayPublicCertPath = '';
    protected string $alipayRootCertPath = '';

    public function setAppId(string $value): void
    {
        $this->appId = $value;
    }

    public function setAppSecretCert(string $value): void
    {
        $this->appSecretCert = $value;
    }

    public function setNotifyUrl(?string $value): void
    {
        $this->notifyUrl = $value;
    }

    public function setReturnUrl(?string $value): void
    {
        $this->returnUrl = $value;
    }

    public function setAppAuthToken(?string $value): void
    {
        $this->appAuthToken = $value;
    }

    public function setServiceProviderId(?string $value): void
    {
        $this->serviceProviderId = $value;
    }

    public function setMode(int $value): void
    {
        $this->mode = $value;
    }

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

    public function getAppId(): string
    {
        return $this->appId;
    }

    public function getAppSecretCert(): string
    {
        return $this->appSecretCert;
    }

    public function getNotifyUrl(): ?string
    {
        return $this->notifyUrl;
    }

    public function getReturnUrl(): ?string
    {
        return $this->returnUrl;
    }

    /**
     * 第三方应用授权 token.
     */
    public function getAppAuthToken(): ?string
    {
        return $this->appAuthToken;
    }

    /**
     * 服务商模式下的服务商 id.
     */
    public function getServiceProviderId(): ?string
    {
        return $this->serviceProviderId;
    }

    public function getMode(): int
    {
        return $this->mode;
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

    /**
     * V2/V3 管道均依赖 `appId`、`appSecretCert`、`appPublicCertPath`、`alipayPublicCertPath`，构造时强制；
     * `alipayRootCertPath` 仅 V2 管道计算 `root_cert_sn` 时需要，懒校验（V3 协议无 root-cert-sn）.
     *
     * @throws InvalidConfigException 缺少必要配置参数
     */
    protected function validateRequired(): void
    {
        $this->validateNotEmpty(
            ['appId', 'appSecretCert', 'appPublicCertPath', 'alipayPublicCertPath'],
            Exception::CONFIG_ALIPAY_INVALID,
            '配置异常: 缺少支付宝配置'
        );
    }
}
