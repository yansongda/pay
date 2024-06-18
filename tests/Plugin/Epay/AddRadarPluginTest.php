<?php

namespace Yansongda\Pay\Tests\Plugin\Epay;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Epay\AddRadarPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class AddRadarPluginTest extends TestCase
{
	protected AddRadarPlugin $plugin;

	protected function setUp(): void
	{
		parent::setUp();

		$this->plugin = new AddRadarPlugin();
	}


	public function testRadarPostNormal()
	{
		$rocket = new Rocket();
		$rocket->setParams([])->setPayload(new Collection(['name' => 'yansongda']));

		$result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

		self::assertEquals('https://epaytest.jsbchina.cn:9999/eis/merchant/merchantServices.htm', (string) $result->getRadar()->getUri());
		self::stringContains('name=yansongda', (string) $result->getRadar()->getBody());
		self::assertEquals('POST', $result->getRadar()->getMethod());
	}
}
