<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

use Yansongda\Pay\Pay;

abstract class AlipayConfig extends AbstractConfig
{
    /**
     * API 版本：V2 网关签名（form 表单，`gateway.do`）.
     */
    public const VERSION_V2 = 'v2';

    /**
     * API 版本：V3 OpenAPI（RESTful `/v3/` 路径 + HTTP 头签名）.
     */
    public const VERSION_V3 = 'v3';

    protected string $appId = '';
    protected string $appSecretCert = '';
    protected ?string $notifyUrl = null;
    protected ?string $returnUrl = null;
    protected ?string $appAuthToken = null;
    protected ?string $serviceProviderId = null;
    protected int $mode = Pay::MODE_NORMAL;

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

    /**
     * 租户 API 版本（由配置类决定：`AlipayV2Config` 为 v2，`AlipayV3Config` 为 v3）.
     */
    abstract public function getVersion(): string;
}
