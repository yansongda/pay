<?php

declare(strict_types=1);

use Yansongda\Artful\Direction\ResponseDirection;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V2\Fund\Transfer\Fund\TransferPlugin;

it('正常', function () {
    $plugin = new TransferPlugin();
    $rocket = (new Rocket())
        ->setParams([]);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload()->toJson();

    expect($result->getDirection())->not->toEqual(ResponseDirection::class);
    expect($payload)->toContain('alipay.fund.trans.uni.transfer');
    expect($payload)->toContain('DIRECT_TRANSFER');
    expect($payload)->toContain('TRANS_ACCOUNT_NO_PWD');
});

