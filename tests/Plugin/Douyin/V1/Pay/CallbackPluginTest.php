<?php

declare(strict_types=1);

namespace Plugin\Douyin\V1\Pay;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Douyin\V1\Pay\CallbackPlugin;
use Yansongda\Pay\Tests\TestCase;

class CallbackPluginTest extends TestCase
{
    private CallbackPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new CallbackPlugin();
    }

    public function testNotifyCallbackIncludePlus()
    {
        $post = '{"msg":"{\"appid\":\"tt226e54d3bd581bf801\",\"cp_orderno\":\"202408041111312119\",\"cp_extra\":\"\",\"way\":\"2\",\"channel_no\":\"\",\"channel_gateway_no\":\"\",\"payment_order_no\":\"\",\"out_channel_order_no\":\"\",\"total_amount\":1,\"status\":\"SUCCESS\",\"seller_uid\":\"73744242495132490630\",\"extra\":\"\",\"item_id\":\"\",\"paid_at\":1722769986,\"message\":\"\",\"order_id\":\"7398108028895054107\"}","msg_signature":"840bdf067c1d6056becfe88735c8ebb7e1ab809c","nonce":"5280","timestamp":"1722769986","type":"payment"}';

        $rocket = new Rocket();
        $rocket->setParams(json_decode($post, true));

        $result = $this->plugin->assembly($rocket, function ($rocket) {return $rocket;});

        self::assertNotEmpty($result->getPayload()->all());
        self::assertNotEmpty($result->getDestination()->all());
    }
}
