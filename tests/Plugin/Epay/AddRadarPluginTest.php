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

		//是否存在Content-Type和User-Agent
		self::assertArrayHasKey('Content-Type', $result->getRadar()->getHeaders());
		self::assertArrayHasKey('User-Agent', $result->getRadar()->getHeaders());
		//验证值
		self::assertEquals('text/html', $result->getRadar()->getHeader('Content-Type')[0]);
		self::assertEquals('yansongda/pay-v3', $result->getRadar()->getHeader('User-Agent')[0]);
	}
}
