<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Stubs\Traits;

use Yansongda\Pay\Traits\WechatTrait;

class WechatTraitStub
{
    use WechatTrait;

    protected static function verifyWechatTimestamp(int $timestamp): void
    {
    }
}
