<?php

declare(strict_types=1);

use Yansongda\Pay\Enum\Alipay\BizCode;

it('暴露业务码值', function () {
    expect(BizCode::DATA_DIGITAL_BIZ_CODE_FACE_VERIFICATION->value)->toBe('DATA_DIGITAL_BIZ_CODE_FACE_VERIFICATION');
    expect(BizCode::DATA_DIGITAL_BIZ_CODE_FACE_CHECK_LIVE->value)->toBe('DATA_DIGITAL_BIZ_CODE_FACE_CHECK_LIVE');
    expect(BizCode::DATA_DIGITAL_BIZ_CODE_OCR->value)->toBe('DATA_DIGITAL_BIZ_CODE_OCR');
    expect(BizCode::FUTURE_TECH_BIZ_FACE_SDK->value)->toBe('FUTURE_TECH_BIZ_FACE_SDK');
});

it('保持枚举声明顺序', function () {
    expect(BizCode::cases())->toBe([
        BizCode::DATA_DIGITAL_BIZ_CODE_FACE_VERIFICATION,
        BizCode::DATA_DIGITAL_BIZ_CODE_FACE_CHECK_LIVE,
        BizCode::DATA_DIGITAL_BIZ_CODE_OCR,
        BizCode::FUTURE_TECH_BIZ_FACE_SDK,
    ]);
});

it('按字符串值 tryFrom', function () {
    expect(BizCode::tryFrom('DATA_DIGITAL_BIZ_CODE_FACE_VERIFICATION'))->toBe(BizCode::DATA_DIGITAL_BIZ_CODE_FACE_VERIFICATION);
    expect(BizCode::tryFrom('FUTURE_TECH_BIZ_FACE_SDK'))->toBe(BizCode::FUTURE_TECH_BIZ_FACE_SDK);
    expect(BizCode::tryFrom('NOT_EXIST'))->toBeNull();
    expect(BizCode::tryFrom(''))->toBeNull();
});
