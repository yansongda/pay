<?php

namespace Yansongda\Pay\Tests\Plugin\Epay\Pay\Scan;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Epay\Pay\Scan\RefundPlugin;
use Yansongda\Pay\Tests\TestCase;

class RefundPluginTest extends TestCase
{
	protected RefundPlugin $plugin;

	protected function setUp(): void
	{
		parent::setUp();

		$this->plugin = new RefundPlugin();
	}

	public function testNormal()
	{
		$rocket = (new Rocket())
			->setParams([]);

		$result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
		self::assertStringContainsString('payRefund', $result->getPayload()->toJson());
		self::assertStringContainsString('deviceNo', $result->getPayload()->toJson());
	}
}
