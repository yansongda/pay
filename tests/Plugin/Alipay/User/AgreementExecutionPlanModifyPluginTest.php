<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\User;

use Yansongda\Pay\Plugin\Alipay\User\AgreementExecutionPlanModifyPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class AgreementExecutionPlanModifyPluginTest extends TestCase
{
    protected $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new AgreementExecutionPlanModifyPlugin();
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertNull($result->getDirection());
        self::assertStringContainsString('alipay.user.agreement.executionplan.modify', $result->getPayload()->toJson());
    }
}
