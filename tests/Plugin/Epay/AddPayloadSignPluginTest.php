<?php

namespace Yansongda\Pay\Tests\Plugin\Epay;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Epay\AddPayloadSignPlugin;
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

	public function testSignNormal()
	{
		$payload = ['outTradeNo'=>'YC202406170003','totalFee'=>0.01,'proInfo'=>'元仓充值','backUrl'=>'http:\/\/127.0.0.1:8000\/epay\/return','createData'=>'20240618','createTime'=>'022522','bizDate'=>'20240618','msgId'=>'16253083-49c4-4142-8c56-997accf3d667','svrCode'=>'','partnerId'=>'6a13eab71c4f4b0aa4757eda6fc59710','channelNo'=>'m','publicKeyCode'=>'00','version'=>'v1.0.0','charset'=>'utf-8','service'=>'atPay'];
		$sign = "bDVcd91eQNVMyf7on1/YewqyNzBdorHy3/BA7L89CKuyDZtf/FId/GbGTBbn1QzGsLB8Vcultv1BxFrMGJUrEF73HO4rHtkAXKu9hna6KmtDCXRVNJw6fjuU9epXdJqE1RSB8f0j5HKDOxD2LBSZB1ZAbmH5v2WMCvx83cYpjOQ=";
		$rocket = new Rocket();
		$rocket->setParams([])->setPayload(new Collection($payload));

		$result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
		self::assertSame($sign, $result->getPayload()->get('sign'));
	}
}
