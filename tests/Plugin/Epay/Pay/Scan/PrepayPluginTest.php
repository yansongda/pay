<?php

namespace Yansongda\Pay\Tests\Plugin\Epay\Pay\Scan;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Epay\Pay\Scan\PrepayPlugin;
use Yansongda\Pay\Tests\TestCase;

class PrepayPluginTest extends TestCase
{

	protected PrepayPlugin $plugin;

	protected function setUp(): void
	{
		parent::setUp();

		$this->plugin = new PrepayPlugin();
	}

	public function testNormal()
	{
		$rocket = (new Rocket())
			->setParams([]);

		$result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
		self::assertStringContainsString('atPay', $result->getPayload()->toJson());
	}
}
