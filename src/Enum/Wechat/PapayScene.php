<?php

declare(strict_types=1);

namespace Yansongda\Pay\Enum\Wechat;

/**
 * 微信委托代扣（papay）`_type` 场景。
 *
 * 语义大于 AppType：`SCAN`/`H5` 属于「无需调起」场景（NATIVE 返回 code_url、MWEB 返回 mweb_url），
 * 其 AppId 选择与 MP 等价（历史行为）。
 *
 * @see web/docs/v3/wechat/pay.md
 */
enum PapayScene: string
{
    /** 公众号 */
    case MP = 'mp';

    /** 小程序 */
    case MINI = 'mini';

    /** APP */
    case APP = 'app';

    /** 扫码（NATIVE）：响应 code_url，无需调起 */
    case SCAN = 'scan';

    /** H5（MWEB）：响应 mweb_url，无需调起 */
    case H5 = 'h5';

    /**
     * 宽松归一化：无法识别返回 null（调用方据此抛既有 `PARAMS_WECHAT_PAPAY_TYPE_NOT_SUPPORTED`）。
     */
    public static function fromLoose(self|string|null $scene): ?self
    {
        return $scene instanceof self ? $scene : self::tryFrom((string) $scene);
    }

    /**
     * 对应的 AppId 类型（SCAN/H5 与 MP 等价，保持历史行为）。
     */
    public function appType(): AppType
    {
        return match ($this) {
            self::MINI => AppType::MINI,
            self::APP => AppType::APP,
            self::MP, self::SCAN, self::H5 => AppType::MP,
        };
    }

    /**
     * 是否需要追加调起插件（SCAN/H5 无需调起）。
     */
    public function requiresInvoke(): bool
    {
        return !in_array($this, [self::SCAN, self::H5], true);
    }
}
