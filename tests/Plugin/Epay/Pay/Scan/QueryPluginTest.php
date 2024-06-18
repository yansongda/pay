<?php

namespace Yansongda\Pay\Tests\Plugin\Epay\Pay\Scan;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Epay\Pay\Scan\QueryPlugin;
use Yansongda\Pay\Tests\TestCase;

class QueryPluginTest extends TestCase
{
	protected QueryPlugin $plugin;

	protected function setUp(): void
	{
		parent::setUp();

		$this->plugin = new QueryPlugin();
	}

	public function testNormal()
	{
		$rocket = (new Rocket())
			->setParams([]);

		$result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
		self::assertStringContainsString('payCheck', $result->getPayload()->toJson());
		self::assertStringContainsString('deviceNo', $result->getPayload()->toJson());
	}
}
