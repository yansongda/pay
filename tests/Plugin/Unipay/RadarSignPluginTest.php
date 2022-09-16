<?php

namespace Yansongda\Pay\Tests\Plugin\Unipay;

use GuzzleHttp\Psr7\Request;
use Yansongda\Pay\Plugin\Unipay\RadarSignPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class RadarSignPluginTest extends TestCase
{
    /**
     * @var \Yansongda\Pay\Plugin\Unipay\RadarSignPlugin
     */
    protected $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new RadarSignPlugin();
    }

    public function testNormal()
    {
        $params = [
            'txnTime' => '20220903065448',
            'txnAmt' => 1,
            'orderId' => 'yansongda20220903065448',
            'version' => '5.1.0',
            'encoding' => 'utf-8',
            'bizType' => '000201',
            'backUrl' => 'https://yansongda.cn/unipay/notify',
            'currencyCode' => '156',
            'txnType' => '01',
            'txnSubType' => '01',
            'accessType' => '0',
            'signature' => '',
            'signMethod' => '01',
            'channelType' => '07',
            'merId' => '777290058167151',
            'frontUrl' => 'https://yansongda.cn/unipay/return',
            'certId' => '69903319369',
        ];

        $rocket = (new Rocket())->setPayload(new Collection($params))->setRadar(new Request('GET', 'https://yansongda.cn'));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();
        $radar = $result->getRadar();

        self::assertEquals('CJ0Hh6WC0YldYMi2cIi7rjOvV4IJyzhqgj8KKNeRWZz+2csPVlDcnP1f4YfykVww6NDimsl2zE+oQAm7lH8qiU5f8ojVH+P62uMe1yqb2WoNlIM45REg6bhfUPvATgecVplnKIPdFUGdRZ97va+ZVbla75HwtskZnDykmr9rkYaSg7PcGuilEwHcb2rV+BNkCi3bi4fYELjYlE1a7Imv/cSLRyXkPS1jzF0HUIUiVA6P98DYh0GbsgqRZqPYsGocFJs9jZoAlu44RygzDKjK/n8iwhHNH61IFaADdGp+uLQsol6kR/DRpF1pZgO7lsuK0YuaL6pfN6dtkJO9prbu6Q==', $payload->get('signature'));
        self::stringContains('CJ0Hh6WC0YldYMi2cIi7rjOvV4IJyzhqgj8KKNeRWZz', (string) $radar->getBody());
    }
}
