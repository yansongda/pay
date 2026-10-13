<?php

declare(strict_types=1);

namespace Yansongda\Pay\Enum;

/**
 * Provider 运行模式（配置项 `mode`）。
 *
 * 配置与读取统一使用枚举：`'mode' => PayMode::SANDBOX`、`$config->getMode()->isService()`。
 * 内部以 int 存储（`AbstractConfig::$mode`）：`toArray()['mode']` 经 getter 返回本枚举，
 * `toJson()` / `json_encode()` 仍输出 `0`/`1`/`2`（backed enum 序列化行为）。
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
     * 非法 mode 的拦截仍由 `AbstractConfig::validateMode()` 负责（抛 CONFIG_PROVIDER_INVALID）；
     * `getMode()` 依赖本方法把已校验的内部 int 转成枚举。
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
