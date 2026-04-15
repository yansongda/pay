<?php

namespace Yansongda\Pay\Tests\Plugin\Unipay\Qra;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Unipay\Qra\AddPayloadSignaturePlugin;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class AddPayloadSignaturePluginTest extends TestCase
{
    protected AddPayloadSignaturePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new AddPayloadSignaturePlugin();
    }

    public function testNormal()
    {
        $payload = [
            'out_trade_no' => 'pos-qra-20240106163401',
            'body' => '测试商品',
            'total_fee' => 1,
            'mch_create_ip' => '127.0.0.1',
            'auth_code' => '131969896307360385',
            'op_device_id' => '123',
            'terminal_info' => json_encode([
                'device_type' => '07',
                'terminal_id' => '123',
            ]),
            'service' => 'unified.trade.micropay',
            'charset' => 'UTF-8',
            'sign_type' => 'MD5',
            'mch_id' => 'QRA29045311KKR1',
            'nonce_str' => 'UhxOr4kzerPGku9wCaVQyfd1zisoAnAm',
            '_aaa' => 'bb',
            'a' => null,
        ];

        $rocket = (new Rocket())->setParams(['_config' => 'qra'])->setPayload($payload);
        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();


        self::assertEquals('DB571C2F75C657B42485CD07470F0FB9', $payload->get('sign'));
    }

    public function testPayloadEmpty()
    {
        $rocket = (new Rocket())->setPayload(new Collection());

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }
}
