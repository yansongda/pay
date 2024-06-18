<?php

namespace Yansongda\Pay\Tests\Plugin\Epay;

use GuzzleHttp\Psr7\Response;
use Yansongda\Artful\Exception\Exception;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Epay\ResponsePlugin;
use Yansongda\Pay\Tests\TestCase;

class ResponsePluginTest extends TestCase
{
	protected ResponsePlugin $plugin;

	protected function setUp(): void
	{
		parent::setUp();

		$this->plugin = new ResponsePlugin();
	}

	public function testNormal()
	{
		$body = 'errCode=&field1=&field2=&field3=&orderNo=20240617144526400259379&orderStatus=1&outTradeNo=YC202406170003&partnerId=6a13eab71c4f4b0aa4757eda6fc59710&payUrl=http://weixintest.jsbchina.cn/epcs/qr/login.htm?qrCode=2018060611052793473720240617144526688568&respBizDate=20240617&respCode=000000&respMsg=交易成功&totalFee=0.01&validTime=2&signType=RSA&sign=jN3Ha6J9UUIe9M0L/XeexEdaRL9GB6nMV12wNC7LQvTS6V4nKHj4Qzw6M8cNsA9L0Tb3QFT83B0qO3FJnruDrcHKqBLZb4FkoKKN/WiDBuA2UZQjG4+CBejoGJWfpkWSsei9tXUk36TB27lc2ZlYXSEwuuDwM7M9yvlYysc3fjg=';

		$rocket = (new Rocket())->setDestinationOrigin(new Response(200, [], $body));
		$result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
		self::assertSame($rocket, $result);
	}

	public function testCodeWrong()
	{
		self::expectException(InvalidResponseException::class);
		self::expectExceptionCode(Exception::RESPONSE_ERROR);
		$body = 'errCode=1&field1=&field2=&field3=&orderNo=20240617144526400259379&orderStatus=1&outTradeNo=YC202406170003&partnerId=6a13eab71c4f4b0aa4757eda6fc59710&payUrl=http://weixintest.jsbchina.cn/epcs/qr/login.htm?qrCode=2018060611052793473720240617144526688568&respBizDate=20240617&respCode=000001&respMsg=交易成功&totalFee=0.01&validTime=2&signType=RSA&sign=jN3Ha6J9UUIe9M0L/XeexEdaRL9GB6nMV12wNC7LQvTS6V4nKHj4Qzw6M8cNsA9L0Tb3QFT83B0qO3FJnruDrcHKqBLZb4FkoKKN/WiDBuA2UZQjG4+CBejoGJWfpkWSsei9tXUk36TB27lc2ZlYXSEwuuDwM7M9yvlYysc3fjg=';

		$rocket = (new Rocket())->setDestinationOrigin(new Response(500, [], $body));

		$result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
		self::assertSame($rocket, $result);
	}

	public function testHttpWrong()
	{
		self::expectException(InvalidResponseException::class);
		self::expectExceptionCode(Exception::RESPONSE_ERROR);
		self::expectExceptionMessage('epay返回状态码异常，请检查参数是否错误');
		$body = 'errCode=1&field1=&field2=&field3=&orderNo=20240617144526400259379&orderStatus=1&outTradeNo=YC202406170003&partnerId=6a13eab71c4f4b0aa4757eda6fc59710&payUrl=http://weixintest.jsbchina.cn/epcs/qr/login.htm?qrCode=2018060611052793473720240617144526688568&respBizDate=20240617&respCode=000000&respMsg=交易成功&totalFee=0.01&validTime=2&signType=RSA&sign=jN3Ha6J9UUIe9M0L/XeexEdaRL9GB6nMV12wNC7LQvTS6V4nKHj4Qzw6M8cNsA9L0Tb3QFT83B0qO3FJnruDrcHKqBLZb4FkoKKN/WiDBuA2UZQjG4+CBejoGJWfpkWSsei9tXUk36TB27lc2ZlYXSEwuuDwM7M9yvlYysc3fjg=';

		$rocket = (new Rocket())->setDestinationOrigin(new Response(500, [], $body));

		$result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
		self::assertSame($rocket, $result);
	}
}
