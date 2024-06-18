<?php

namespace Yansongda\Pay\Tests\Plugin\Epay;

use GuzzleHttp\Psr7\ServerRequest;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Epay\CallbackPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class CallbackPluginTest extends TestCase
{
	protected CallbackPlugin $plugin;

	protected function setUp(): void
	{
		parent::setUp();

		$this->plugin = new CallbackPlugin();
	}

	public function testNormal()
	{
		$payload = [
			'partnerId'=> '6a13eab71c4f4b0aa4757eda6fc59710',
			'orderStatus'=> '1',
			'totalFee'=> '0.02',
			'outTradeNo'=> 'RC240613164110030316',
			'orderNo'=> '20240613164114400729509',
			'field1'=> '2',
			'field2'=> '',
			'field3'=> '20240613164139|20240613164134400800219',
			'signType'=> 'RSA',
			'sign'=> 'DPKX4mZAVd/LwMDOt1OJgryBuPeH78y7B78smze+m+vvzae5MBf0O3BoTvVJQHD/RPVftHVvnYHeKvIjCC2bCrxoY9Sv2N8Hbr5HfjIikk0a2qaIQp6TTvecMP9JitzSuZP+sih+uxMkRM5Nrg8weGbePaQ6nODNWiSGDhV+Jq0='
		];
		$request = new ServerRequest('POST', 'http://localhost');
		$request = $request->withParsedBody($payload);

		$rocket = new Rocket();
		$rocket->setParams(['request'=>Collection::wrap($request->getParsedBody())]);

		$result = $this->plugin->assembly($rocket, function ($rocket) {return $rocket;});

		self::assertNotEmpty($result->getPayload()->all());
	}

}
