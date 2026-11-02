<?php

declare(strict_types=1);

use Yansongda\Pay\Enum\Alipay\ProductCode;

it('暴露产品码值', function () {
    expect(ProductCode::FAST_INSTANT_TRADE_PAY->value)->toBe('FAST_INSTANT_TRADE_PAY');
    expect(ProductCode::QUICK_WAP_WAY->value)->toBe('QUICK_WAP_WAY');
    expect(ProductCode::JSAPI_PAY->value)->toBe('JSAPI_PAY');
    expect(ProductCode::FACE_TO_FACE_PAYMENT->value)->toBe('FACE_TO_FACE_PAYMENT');
    expect(ProductCode::PREAUTH_PAY->value)->toBe('PREAUTH_PAY');
    expect(ProductCode::GENERAL_WITHHOLDING->value)->toBe('GENERAL_WITHHOLDING');
    expect(ProductCode::STD_RED_PACKET->value)->toBe('STD_RED_PACKET');
    expect(ProductCode::STD_APP_TRANSFER->value)->toBe('STD_APP_TRANSFER');
    expect(ProductCode::TRANS_ACCOUNT_NO_PWD->value)->toBe('TRANS_ACCOUNT_NO_PWD');
    expect(ProductCode::FACE->value)->toBe('FACE');
});

it('保持枚举声明顺序', function () {
    expect(ProductCode::cases())->toBe([
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
    ]);
});

it('按字符串值 tryFrom', function () {
    expect(ProductCode::tryFrom('FAST_INSTANT_TRADE_PAY'))->toBe(ProductCode::FAST_INSTANT_TRADE_PAY);
    expect(ProductCode::tryFrom('FACE'))->toBe(ProductCode::FACE);
    expect(ProductCode::tryFrom('NOT_EXIST'))->toBeNull();
    expect(ProductCode::tryFrom(''))->toBeNull();
});
