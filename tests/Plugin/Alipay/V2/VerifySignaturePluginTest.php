<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\V2;

use Yansongda\Artful\Exception\Exception;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Plugin\Alipay\V2\VerifySignaturePlugin;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class VerifySignaturePluginTest extends TestCase
{
    protected VerifySignaturePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new VerifySignaturePlugin();
    }

    public function testSignNormal()
    {
        $destination = [
            "_sign" => 'eITxP5fZiJPB2+vZb90IRkv2iARxeNx/6Omxk7FStqflhG5lMoCvGjo2FZ6Szo1bGBMBReazZuqLaqsgomWAUO9onMVurB3enLbRvwUlpE7XEZaxk/sJYjgc2Y7pIAenvnLL9PEAiXmvUvuinUlvS9J2r1XysC0p/2wu7kEJ/GgZpFDIIYY9mdM6U1rGbi+RvirQXtQHmaEuuJWLA75NR1bvfG3L8znzW9xz1kOQqOWsQmD/bF1CDWbozNLwLCUmClRJz0Fj4mUYRF0zbW2VP8ZgHu1YvVKJ2+dWC9b+0o94URk7psIpc5NjiOM9Jsn6aoC2CfrJ/sqFMRCkYWzw6A==',
            "code" => "10000",
            "msg" => "Success",
            "order_id" => "20231220110070000002150000657610",
            "out_biz_no" => "2023122022560000",
            "pay_date" => "2023-12-20 22:56:33",
            "pay_fund_order_id" => "20231220110070001502150000660902",
            "status" => "SUCCESS",
            "trans_amount" => "0.01",
        ];

        $rocket = (new Rocket())
            ->setDestination(new Collection($destination));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertSame($rocket, $result);
    }

    public function testSignWrong()
    {
        $destination = [
            "_sign" => 'AeITxP5fZiJPB2+vZb90IRkv2iARxeNx/6Omxk7FStqflhG5lMoCvGjo2FZ6Szo1bGBMBReazZuqLaqsgomWAUO9onMVurB3enLbRvwUlpE7XEZaxk/sJYjgc2Y7pIAenvnLL9PEAiXmvUvuinUlvS9J2r1XysC0p/2wu7kEJ/GgZpFDIIYY9mdM6U1rGbi+RvirQXtQHmaEuuJWLA75NR1bvfG3L8znzW9xz1kOQqOWsQmD/bF1CDWbozNLwLCUmClRJz0Fj4mUYRF0zbW2VP8ZgHu1YvVKJ2+dWC9b+0o94URk7psIpc5NjiOM9Jsn6aoC2CfrJ/sqFMRCkYWzw6A==',
            "code" => "10000",
            "msg" => "Success",
            "order_id" => "20231220110070000002150000657610",
            "out_biz_no" => "2023122022560000",
            "pay_date" => "2023-12-20 22:56:33",
            "pay_fund_order_id" => "20231220110070001502150000660902",
            "status" => "SUCCESS",
            "trans_amount" => "0.01",
        ];

        $rocket = (new Rocket())
            ->setDestination(new Collection($destination));

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(\Yansongda\Pay\Exception\Exception::SIGN_ERROR);

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testSignContentWrong()
    {
        $rocket = new Rocket();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::RESPONSE_EMPTY);

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }
}
