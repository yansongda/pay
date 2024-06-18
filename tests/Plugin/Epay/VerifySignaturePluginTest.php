<?php

namespace Yansongda\Pay\Tests\Plugin\Epay;

use GuzzleHttp\Psr7\Response;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Plugin\Epay\VerifySignaturePlugin;
use Yansongda\Pay\Tests\TestCase;

class VerifySignaturePluginTest extends TestCase
{
	protected VerifySignaturePlugin $plugin;

	protected function setUp(): void
	{
		parent::setUp();

		$this->plugin = new VerifySignaturePlugin();
	}

	public function testSignNormal()
	{
		$body = 'errCode=&field1=&field2=&field3=&orderNo=20240617144526400259379&orderStatus=1&outTradeNo=YC202406170003&partnerId=6a13eab71c4f4b0aa4757eda6fc59710&payUrl=http://weixintest.jsbchina.cn/epcs/qr/login.htm?qrCode=2018060611052793473720240617144526688568&respBizDate=20240617&respCode=000000&respMsg=交易成功&totalFee=0.01&validTime=2&signType=RSA&sign=jN3Ha6J9UUIe9M0L/XeexEdaRL9GB6nMV12wNC7LQvTS6V4nKHj4Qzw6M8cNsA9L0Tb3QFT83B0qO3FJnruDrcHKqBLZb4FkoKKN/WiDBuA2UZQjG4+CBejoGJWfpkWSsei9tXUk36TB27lc2ZlYXSEwuuDwM7M9yvlYysc3fjg=';

		$rocket = (new Rocket())->setDestinationOrigin(new Response(200, [], $body));

		$result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
		self::assertSame($rocket, $result);
	}

	public function testSignWrong()
	{
		self::expectException(InvalidSignException::class);
		$body = 'errCode=1&field1=&field2=&field3=&orderNo=20240617144526400259379&orderStatus=1&outTradeNo=YC202406170003&partnerId=6a13eab71c4f4b0aa4757eda6fc59710&payUrl=http://weixintest.jsbchina.cn/epcs/qr/login.htm?qrCode=2018060611052793473720240617144526688568&respBizDate=20240617&respCode=000000&respMsg=交易成功&totalFee=0.01&validTime=2&signType=RSA&sign=jN3Ha6J9UUIe9M0L/XeexEdaRL9GB6nMV12wNC7LQvTS6V4nKHj4Qzw6M8cNsA9L0Tb3QFT83B0qO3FJnruDrcHKqBLZb4FkoKKN/WiDBuA2UZQjG4+CBejoGJWfpkWSsei9tXUk36TB27lc2ZlYXSEwuuDwM7M9yvlYysc3fjg=';

		$rocket = (new Rocket())->setDestinationOrigin(new Response(200, [], $body));

		$result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
		self::assertSame($rocket, $result);
	}
}
