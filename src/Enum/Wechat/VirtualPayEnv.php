<?php

declare(strict_types=1);

namespace Yansongda\Pay\Enum\Wechat;

/**
 * 微信虚拟支付环境（`env` 参数）。
 *
 * @see https://developers.weixin.qq.com/miniprogram/dev/platform-capabilities/business-capabilities/virtual-payment.html
 */
enum VirtualPayEnv: int
{
    /** 正式环境（默认） */
    case PRODUCTION = 0;

    /** 沙箱环境 */
    case SANDBOX = 1;

    /**
     * 宽松归一化：未知值回落 PRODUCTION（保持历史行为）。
     */
    public static function fromLoose(int|self|string|null $env): self
    {
        if ($env instanceof self) {
            return $env;
        }

        return is_numeric($env) ? (self::tryFrom((int) $env) ?? self::PRODUCTION) : self::PRODUCTION;
    }

    /** 是否为沙箱环境 */
    public function isSandbox(): bool
    {
        return self::SANDBOX === $this;
    }
}
