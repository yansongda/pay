<?php

declare(strict_types=1);

namespace Yansongda\Pay\Enum\Alipay;

/**
 * 支付宝数字身份/人脸业务 `biz_content.biz_code`（业务码）。
 *
 * @see https://opendocs.alipay.com/open/04jg6r
 */
enum BizCode: string
{
    /** 人脸验证初始化（datadigital.fincloud.generalsaas.face.verification.initialize） */
    case DATA_DIGITAL_BIZ_CODE_FACE_VERIFICATION = 'DATA_DIGITAL_BIZ_CODE_FACE_VERIFICATION';

    /** 人脸核身/活体检测初始化（datadigital.fincloud.generalsaas.face.check.initialize） */
    case DATA_DIGITAL_BIZ_CODE_FACE_CHECK_LIVE = 'DATA_DIGITAL_BIZ_CODE_FACE_CHECK_LIVE';

    /** OCR 识别初始化（datadigital.fincloud.generalsaas.ocr.mobile.initialize） */
    case DATA_DIGITAL_BIZ_CODE_OCR = 'DATA_DIGITAL_BIZ_CODE_OCR';

    /** 人脸认证 H5 初始化（datadigital.fincloud.generalsaas.face.certify.initialize，Face SDK） */
    case FUTURE_TECH_BIZ_FACE_SDK = 'FUTURE_TECH_BIZ_FACE_SDK';
}
