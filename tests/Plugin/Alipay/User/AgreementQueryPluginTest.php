<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\User;

use Yansongda\Pay\Contract\DirectionInterface;
use Yansongda\Pay\Plugin\Alipay\User\AgreementQueryPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class AgreementQueryPluginTest extends TestCase
{
    protected $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new AgreementQueryPlugin();
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals(DirectionInterface::class, $result->getDirection());
        self::assertStringContainsString('alipay.user.agreement.query', $result->getPayload()->toJson());
        self::assertStringContainsString('CYCLE_PAY_AUTH_P', $result->getPayload()->toJson());
    }
}
