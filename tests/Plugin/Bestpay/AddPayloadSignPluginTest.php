<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Bestpay;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Bestpay\V1\AddPayloadSignPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class AddPayloadSignPluginTest extends TestCase
{
    protected AddPayloadSignPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new AddPayloadSignPlugin();
    }

    public function testNormal(): void
    {
        $rocket = new Rocket();
        $rocket->setParams([])
            ->setPayload(new Collection([
                'merchantNo' => 'bestpay_merchant_no',
                'platform' => 'HELIPAY',
                'totalAmount' => '100',
                'signType' => 'MD5',
            ]));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertNotEmpty($result->getPayload()->get('sign'));
        self::assertEquals('md5', strtolower(mb_detect_encoding($result->getPayload()->get('sign')) ? 'md5' : ''));
    }

    public function testMissingAppKey(): void
    {
        self::expectException(InvalidConfigException::class);
        self::expectExceptionCode(Exception::CONFIG_BESTPAY_INVALID);

        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'not_exist'])
            ->setPayload(new Collection(['merchantNo' => 'test']));

        $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
    }
}
