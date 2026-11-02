<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Support\Apple;

/**
 * Apple Pay 支付数据 fixture。
 *
 * 原始支付数据字段（PAN / 有效期 / 币种 / 金额 / 加密密文），供 AppleTrait 验签解密单测复用。
 */
final class PaymentData
{
    public const PAYMENT_DATA = [
        'applicationPrimaryAccountNumber' => '378282246310005',
        'applicationExpirationDate' => '290101',
        'currencyCode' => '156',
        'transactionAmount' => 100,
        'cardholderName' => 'Yansongda Pay',
        'deviceManufacturerIdentifier' => '050103030300',
        'paymentDataType' => '3DSecure',
        'paymentData' => [
            'onlinePaymentCryptogram' => 'AgAAAAAACcm3BAYGAgIIAw==',
            'eciIndicator' => '05',
        ],
    ];
}
