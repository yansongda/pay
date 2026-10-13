<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Enum\Alipay;

use Yansongda\Pay\Enum\Alipay\BizCode;
use Yansongda\Pay\Tests\TestCase;

class BizCodeTest extends TestCase
{
    public function testValues(): void
    {
        self::assertSame('DATA_DIGITAL_BIZ_CODE_FACE_VERIFICATION', BizCode::DATA_DIGITAL_BIZ_CODE_FACE_VERIFICATION->value);
        self::assertSame('DATA_DIGITAL_BIZ_CODE_FACE_CHECK_LIVE', BizCode::DATA_DIGITAL_BIZ_CODE_FACE_CHECK_LIVE->value);
        self::assertSame('DATA_DIGITAL_BIZ_CODE_OCR', BizCode::DATA_DIGITAL_BIZ_CODE_OCR->value);
        self::assertSame('FUTURE_TECH_BIZ_FACE_SDK', BizCode::FUTURE_TECH_BIZ_FACE_SDK->value);
    }

    public function testCases(): void
    {
        self::assertSame(
            [
                BizCode::DATA_DIGITAL_BIZ_CODE_FACE_VERIFICATION,
                BizCode::DATA_DIGITAL_BIZ_CODE_FACE_CHECK_LIVE,
                BizCode::DATA_DIGITAL_BIZ_CODE_OCR,
                BizCode::FUTURE_TECH_BIZ_FACE_SDK,
            ],
            BizCode::cases()
        );
    }

    public function testTryFrom(): void
    {
        self::assertSame(BizCode::DATA_DIGITAL_BIZ_CODE_FACE_VERIFICATION, BizCode::tryFrom('DATA_DIGITAL_BIZ_CODE_FACE_VERIFICATION'));
        self::assertSame(BizCode::FUTURE_TECH_BIZ_FACE_SDK, BizCode::tryFrom('FUTURE_TECH_BIZ_FACE_SDK'));
        self::assertNull(BizCode::tryFrom('NOT_EXIST'));
        self::assertNull(BizCode::tryFrom(''));
    }
}
