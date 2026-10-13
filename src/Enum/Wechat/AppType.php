<?php

declare(strict_types=1);

namespace Yansongda\Pay\Enum\Wechat;

/**
 * 微信支付 AppId 类型（`_type` 参数）。
 *
 * 用于在 mp/mini/app 三套 AppId 中挑选本次请求使用的 AppId；未传时默认公众号（MP）。
 *
 * @see web/docs/v3/wechat/pay.md
 */
enum AppType: string
{
    /** 公众号（默认） */
    case MP = 'mp';

    /** 小程序 */
    case MINI = 'mini';

    /** APP */
    case APP = 'app';

    /**
     * 宽松归一化：枚举原样返回，未知/空值回落 MP（保持历史行为，不抛异常）。
     */
    public static function fromLoose(self|string|null $type): self
    {
        if ($type instanceof self) {
            return $type;
        }

        return self::tryFrom((string) $type) ?? self::MP;
    }
}
