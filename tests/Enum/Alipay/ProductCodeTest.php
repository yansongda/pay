<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Enum\Alipay;

use Yansongda\Pay\Enum\Alipay\ProductCode;
use Yansongda\Pay\Tests\TestCase;

class ProductCodeTest extends TestCase
{
    public function testValues(): void
    {
        self::assertSame('FAST_INSTANT_TRADE_PAY', ProductCode::FAST_INSTANT_TRADE_PAY->value);
        self::assertSame('QUICK_WAP_WAY', ProductCode::QUICK_WAP_WAY->value);
        self::assertSame('JSAPI_PAY', ProductCode::JSAPI_PAY->value);
        self::assertSame('FACE_TO_FACE_PAYMENT', ProductCode::FACE_TO_FACE_PAYMENT->value);
        self::assertSame('PREAUTH_PAY', ProductCode::PREAUTH_PAY->value);
        self::assertSame('GENERAL_WITHHOLDING', ProductCode::GENERAL_WITHHOLDING->value);
        self::assertSame('STD_RED_PACKET', ProductCode::STD_RED_PACKET->value);
        self::assertSame('STD_APP_TRANSFER', ProductCode::STD_APP_TRANSFER->value);
        self::assertSame('TRANS_ACCOUNT_NO_PWD', ProductCode::TRANS_ACCOUNT_NO_PWD->value);
        self::assertSame('FACE', ProductCode::FACE->value);
    }

    public function testCases(): void
    {
        self::assertSame(
            [
                ProductCode::FAST_INSTANT_TRADE_PAY,
                ProductCode::QUICK_WAP_WAY,
                ProductCode::JSAPI_PAY,
                ProductCode::FACE_TO_FACE_PAYMENT,
                ProductCode::PREAUTH_PAY,
                ProductCode::GENERAL_WITHHOLDING,
                ProductCode::STD_RED_PACKET,
                ProductCode::STD_APP_TRANSFER,
                ProductCode::TRANS_ACCOUNT_NO_PWD,
                ProductCode::FACE,
            ],
            ProductCode::cases()
        );
    }

    public function testTryFrom(): void
    {
        self::assertSame(ProductCode::FAST_INSTANT_TRADE_PAY, ProductCode::tryFrom('FAST_INSTANT_TRADE_PAY'));
        self::assertSame(ProductCode::FACE, ProductCode::tryFrom('FACE'));
        self::assertNull(ProductCode::tryFrom('NOT_EXIST'));
        self::assertNull(ProductCode::tryFrom(''));
    }
}
