<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Alipay\V2\ResponsePlugin;
use Yansongda\Supports\Collection;

it('normal', function () {
    $plugin = new ResponsePlugin();
    $destination = [
        'alipay_trade_query_response' => [
            'code' => '10000',
            'msg' => 'Success',
            'buyer_logon_id' => 'ghd***@sandbox.com',
            'buyer_pay_amount' => '0.00',
            'buyer_user_id' => '2088102174698127',
            'buyer_user_type' => 'PRIVATE',
            'invoice_amount' => '0.00',
            'out_trade_no' => 'yansongda-1622986519',
            'point_amount' => '0.00',
            'receipt_amount' => '0.00',
            'send_pay_date' => '2021-06-06 21:35:40',
            'total_amount' => '0.01',
            'trade_no' => '2021060622001498120501382075',
            'trade_status' => 'TRADE_SUCCESS',
        ],
        'sign' => '123',
    ];

    $rocket = (new Rocket())
        ->mergePayload(['method' => 'alipay.trade.query'])
        ->setDestination(new Collection($destination));

    $result = $plugin->assembly($rocket, function ($rocket) {return $rocket; });

    expect($result->getDestination()->all())->toEqual(array_merge(['_sign' => '123'], $destination['alipay_trade_query_response']));
});

it('error response with no method key', function () {
    $plugin = new ResponsePlugin();
    $destination = [
        'alipay_trade_query_response' => [
            'code' => '10000',
            'msg' => 'Success',
            'buyer_logon_id' => 'ghd***@sandbox.com',
            'buyer_pay_amount' => '0.00',
            'buyer_user_id' => '2088102174698127',
            'buyer_user_type' => 'PRIVATE',
            'invoice_amount' => '0.00',
            'out_trade_no' => 'yansongda-1622986519',
            'point_amount' => '0.00',
            'receipt_amount' => '0.00',
            'send_pay_date' => '2021-06-06 21:35:40',
            'total_amount' => '0.01',
            'trade_no' => '2021060622001498120501382075',
            'trade_status' => 'TRADE_SUCCESS',
        ],
        'sign' => '123',
    ];

    $rocket = (new Rocket())
        ->mergePayload(['method' => 'not.exist'])
        ->setDestination(new Collection($destination));

    $result = $plugin->assembly($rocket, function ($rocket) {return $rocket; });

    expect($result->getDestination()->all())->toEqual(array_merge(['_sign' => '123'], $destination));
});

it('error response with empty sign key', function () {
    $plugin = new ResponsePlugin();
    $this->expectException(InvalidResponseException::class);
    $this->expectExceptionCode(Exception::RESPONSE_BUSINESS_CODE_WRONG);
    $this->expectExceptionMessage('支付宝网关响应异常: 无效的AppID参数');

    $destination = [
        'alipay_fund_trans_uni_transfer_response' => [
            'code' => '40002',
            'msg' => 'Invalid Arguments',
            'sub_code' => 'isv.invalid-app-id',
            'sub_msg' => '无效的AppID参数',
        ],
        'sign' => '',
    ];

    $rocket = (new Rocket())
        ->mergePayload(['method' => 'alipay_fund_trans_uni_transfer'])
        ->setDestination(new Collection($destination));

    $plugin->assembly($rocket, function ($rocket) {return $rocket; });
});

it('encrypted response params', function () {
    $plugin = new ResponsePlugin();
    $destination = [
        'alipay_user_info_share_response' => 'base64密文串',
        'sign' => 'x',
        'sign_type' => 'RSA2',
    ];

    $rocket = (new Rocket())
        ->mergePayload(['method' => 'alipay.user.info.share'])
        ->setDestination(new Collection($destination));

    $result = $plugin->assembly($rocket, function ($rocket) {return $rocket; });

    expect($result->getDestination()->all())->toEqual(['_sign' => 'x', '_cipher' => 'base64密文串']);
});

it('encrypted response without sign', function () {
    $plugin = new ResponsePlugin();
    $this->expectException(InvalidResponseException::class);
    $this->expectExceptionCode(Exception::RESPONSE_BUSINESS_CODE_WRONG);
    $this->expectExceptionMessage('支付宝网关响应异常: 响应为加密密文但未包含签名');

    $destination = [
        'alipay_user_info_share_response' => 'base64密文串',
        'sign' => '',
    ];

    $rocket = (new Rocket())
        ->mergePayload(['method' => 'alipay.user.info.share'])
        ->setDestination(new Collection($destination));

    $plugin->assembly($rocket, function ($rocket) {return $rocket; });
});

