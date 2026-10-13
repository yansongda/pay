<?php

declare(strict_types=1);

namespace Yansongda\Pay\Enum;

use Yansongda\Pay\Pay;

/**
 * Provider 运行模式（配置项 `mode`）。
 *
 * 数值与 {@see Pay::MODE_NORMAL} / {@see Pay::MODE_SANDBOX} / {@see Pay::MODE_SERVICE} 一一对应，
 * 常量保留以便平滑迁移：`'mode' => PayMode::SANDBOX` 与 `'mode' => Pay::MODE_SANDBOX` 等价。
 */
enum PayMode: int
{
    /** 正常模式（生产） */
    case NORMAL = 0;

    /** 沙箱模式 */
    case SANDBOX = 1;

    /** 服务商模式 */
    case SERVICE = 2;

    /**
     * 宽松归一化：支持枚举 / 整数 / 数字字符串，未知值回落 NORMAL。
     *
     * 非法 mode 的拦截仍由 `AbstractConfig::validateMode()` 负责（抛 CONFIG_PROVIDER_INVALID）。
     */
    public static function fromLoose(int|self|string|null $mode): self
    {
        if ($mode instanceof self) {
            return $mode;
        }

        return is_numeric($mode) ? (self::tryFrom((int) $mode) ?? self::NORMAL) : self::NORMAL;
    }

    /** 是否为正常模式 */
    public function isNormal(): bool
    {
        return self::NORMAL === $this;
    }

    /** 是否为沙箱模式 */
    public function isSandbox(): bool
    {
        return self::SANDBOX === $this;
    }

    /** 是否为服务商模式 */
    public function isService(): bool
    {
        return self::SERVICE === $this;
    }
}
