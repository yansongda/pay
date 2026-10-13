<?php

declare(strict_types=1);

namespace Yansongda\Pay\Enum\Wechat;

/**
 * 微信分账接收方类型（`receivers[].type` / `type`）。
 *
 * @see src/Plugin/Wechat/V3/Extend/ProfitSharing/AddReceiverPlugin.php
 */
enum ReceiverType: string
{
    /** 商户号 */
    case MERCHANT_ID = 'MERCHANT_ID';

    /** 个人 OpenID（与请求 AppId 同主体） */
    case PERSONAL_OPENID = 'PERSONAL_OPENID';

    /** 个人子商户 OpenID（服务商模式，需额外补充 `sub_appid`） */
    case PERSONAL_SUB_OPENID = 'PERSONAL_SUB_OPENID';

    /** 是否为需要补充 `sub_appid` 的子商户 OpenID 类型 */
    public function isSubOpenid(): bool
    {
        return self::PERSONAL_SUB_OPENID === $this;
    }

    /**
     * 宽松归一化：无法识别返回 null（不抛异常，避免影响历史报文透传）。
     */
    public static function fromLoose(self|string|null $type): ?self
    {
        return $type instanceof self ? $type : self::tryFrom((string) $type);
    }
}
