<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Alipay\V3\ResponsePlugin;

it('2xx 通过', function () {
    $plugin = new ResponsePlugin();
    foreach ([200, 201, 299] as $statusCode) {
        $rocket = (new Rocket())->setDestinationOrigin(new Response($statusCode, [], json_encode(['code' => '10000'])));
        $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

        expect($result)->toBe($rocket);
    }
});

it('非 2xx 抛出异常', function () {
    $plugin = new ResponsePlugin();
    $rocket = (new Rocket())->setDestinationOrigin(
        new Response(400, [], json_encode(['code' => 'INVALID_PARAMETER', 'message' => '参数错误', 'links' => 'https://example.com/solution']))
    );

    $plugin->assembly($rocket, fn ($rocket) => $rocket);
})->throws(InvalidResponseException::class, '支付宝返回状态码异常: [INVALID_PARAMETER] 参数错误，请检查参数是否错误', Exception::RESPONSE_CODE_WRONG);

it('非 2xx 且无错误 body 抛出异常', function () {
    $plugin = new ResponsePlugin();
    $rocket = (new Rocket())->setDestinationOrigin(new Response(500, [], ''));

    $plugin->assembly($rocket, fn ($rocket) => $rocket);
})->throws(InvalidResponseException::class, '支付宝返回状态码异常，请检查参数是否错误', Exception::RESPONSE_CODE_WRONG);

