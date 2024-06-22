<?php

namespace Yansongda\Pay\Tests\Plugin\Jsb;

use Yansongda\Artful\Packer\QueryPacker;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Jsb\StartPlugin;
use Yansongda\Pay\Tests\TestCase;

class StartPluginTest extends TestCase
{
	protected StartPlugin $plugin;

	protected function setUp(): void
	{
		parent::setUp();

		$this->plugin = new StartPlugin();
	}

	public function testNormal()
	{
		$rocket = new Rocket();

		$result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
		$payload = $result->getPayload();
		self::assertEquals('6a13eab71c4f4b0aa4757eda6fc59710', $payload->get('partnerId'));
		self::assertEquals('v1.0.0', $payload->get('version'));
		self::assertEquals('utf-8', $payload->get('charset'));
		self::assertEquals(date('Ymd'), $payload->get('createData'));
		self::assertEquals(date('His'), $payload->get('createTime'));
		self::assertEquals(date('Ymd'), $payload->get('bizDate'));
		self::assertEquals(QueryPacker::class, $result->getPacker());
	}
}
