<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Douyin\V1\Trade\Pay;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Plugin\Douyin\V1\Trade\Pay\CallbackPlugin;
use Yansongda\Pay\Tests\TestCase;

class CallbackPluginTest extends TestCase
{
    protected CallbackPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new CallbackPlugin();
    }

    public function testMissingSign(): void
    {
        $rocket = new Rocket();
        $rocket->setParams([
            '_config' => 'trade',
            'timestamp' => '1234567890',
            'nonce' => 'random_nonce',
            'msg' => '{"order_id":"test_001"}',
        ]);

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_EMPTY);

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testInvalidSign(): void
    {
        $rocket = new Rocket();
        $rocket->setParams([
            '_config' => 'trade',
            'timestamp' => '1234567890',
            'nonce' => 'random_nonce',
            'msg' => '{"order_id":"test_001"}',
            'msg_signature' => 'invalid_signature',
        ]);

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_ERROR);

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testValidSign(): void
    {
        $appSecret = 'tt_trade_app_secret';
        $timestamp = '1234567890';
        $nonce = 'random_nonce';
        $msg = '{"order_id":"test_001"}';

        $values = [$appSecret, $timestamp, $nonce, $msg];
        sort($values, SORT_STRING);
        $sign = sha1(implode('', $values));

        $rocket = new Rocket();
        $rocket->setParams([
            '_config' => 'trade',
            'timestamp' => $timestamp,
            'nonce' => $nonce,
            'msg' => $msg,
            'msg_signature' => $sign,
        ]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertNotNull($result->getPayload());
        self::assertEquals($sign, $result->getPayload()->get('msg_signature'));
    }
}
