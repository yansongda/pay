<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

use Yansongda\Supports\Traits\Accessable;
use Yansongda\Supports\Traits\Serializable;

class WechatConfigVirtualPay
{
    use Accessable;
    use Serializable;

    private ?string $appKey = null;

    private ?string $sandboxAppKey = null;

    private ?string $offerId = null;

    private ?string $encodingAesKey = null;

    private ?string $callbackToken = null;

    public function setAppKey(?string $value): void
    {
        $this->appKey = $value;
    }

    public function getAppKey(int $env = 0): ?string
    {
        if (1 === $env && null !== $this->sandboxAppKey) {
            return $this->sandboxAppKey;
        }

        return $this->appKey;
    }

    public function setSandboxAppKey(?string $value): void
    {
        $this->sandboxAppKey = $value;
    }

    public function getSandboxAppKey(): ?string
    {
        return $this->sandboxAppKey;
    }

    public function setOfferId(?string $value): void
    {
        $this->offerId = $value;
    }

    public function getOfferId(): ?string
    {
        return $this->offerId;
    }

    public function setEncodingAesKey(?string $value): void
    {
        $this->encodingAesKey = $value;
    }

    public function getEncodingAesKey(): ?string
    {
        return $this->encodingAesKey;
    }

    public function setCallbackToken(?string $value): void
    {
        $this->callbackToken = $value;
    }

    public function getCallbackToken(): ?string
    {
        return $this->callbackToken;
    }
}
