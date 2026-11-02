<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Stubs;

use Yansongda\Pay\Traits\AllinpayTrait;

/**
 * 通联支付 Trait 桩：暴露 Trait 静态方法供 Provider / Trait 用例调用。
 */
class AllinpayTraitHelper
{
    use AllinpayTrait;
}
