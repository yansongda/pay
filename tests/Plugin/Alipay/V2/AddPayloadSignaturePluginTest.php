<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V2\AddPayloadSignaturePlugin;
use Yansongda\Supports\Collection;

it('签名正常', function () {
    $plugin = new AddPayloadSignaturePlugin();
    $payload = [
        'app_id' => '9021000122682882',
        'method' => 'alipay.trade.query',
        'format' => 'JSON',
        'return_url' => 'http://hk.ysdor.cn/index.php',
        'charset' => 'utf-8',
        'sign_type' => 'RSA2',
        'timestamp' => '2023-12-21 06:54:37',
        'version' => '1.0',
        'notify_url' => 'http://hk.ysdor.cn/index.php',
        'app_cert_sn' => 'e90dd23a37c5c7b616e003970817ff82',
        'alipay_root_cert_sn' => '687b59193f3f462dd5336e5abf83c5d8_02941eef3187dddf3d3b83462e1dfcf6',
        'biz_content' => '{"out_trade_no":"1703141270"}',
    ];
    $sign = 'FrzUwXziVWK2x5glZkGoQ6dGR87O33APv3D/lWjVqPrFnMwcYjOYyln1B1bw5VuOwokLRmqcwWuTumBg8Q+n4FkVR3DMCvL8ywTBp5+6pRAETfhFop4m+r6jI+dgOavwyoZ69CWCzCcEyb0vMjgrV2eoSI4bgLjJyPcGpZ+iDBTSvE6eVxs05bUjainAOB5NnAvME06nkaxGLB4qwF9c3SQoiZt7QcMRdyy6mRItZxRaEjMm6qX8dfPuuquJC4wpTRk8PQeu5EMR4yyc1atxso3CMovjkGr5LzDtqLNseyqqorYW+Gfu3ugHSs9aqkwAAysg79O6TsvuFa9OHq64Tg==';

    $rocket = new Rocket();
    $rocket->setParams([])->setPayload(new Collection($payload));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->get('sign'))->toBe($sign);
});

