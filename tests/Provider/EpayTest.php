<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Provider;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Artful\Contract\HttpClientInterface;
use Yansongda\Artful\Exception\Exception;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Epay\AddPayloadSignPlugin;
use Yansongda\Pay\Plugin\Epay\AddRadarPlugin;
use Yansongda\Pay\Plugin\Epay\ResponsePlugin;
use Yansongda\Pay\Plugin\Epay\StartPlugin;
use Yansongda\Pay\Plugin\Epay\VerifySignaturePlugin;
use Yansongda\Pay\Tests\Stubs\Plugin\FooPluginStub;
use Yansongda\Pay\Tests\TestCase;

class EpayTest extends TestCase
{
    public function testShortcutNotFound()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_SHORTCUT_INVALID);

        Pay::epay()->foo();
    }

    public function testMergeCommonPlugins()
    {
        Pay::config([]);
        $plugins = [FooPluginStub::class];

        self::assertEquals(array_merge(
            [StartPlugin::class],
            $plugins,
            [AddPayloadSignPlugin::class, AddRadarPlugin::class, VerifySignaturePlugin::class, ResponsePlugin::class, ParserPlugin::class],
        ), Pay::epay()->mergeCommonPlugins($plugins));
    }

	public function testScan()
	{
		$response = 'errCode=&field1=&field2=&field3=&orderNo=20240617144526400259379&orderStatus=1&outTradeNo=YC202406170003&partnerId=6a13eab71c4f4b0aa4757eda6fc59710&payUrl=http://weixintest.jsbchina.cn/epcs/qr/login.htm?qrCode=2018060611052793473720240617144526688568&respBizDate=20240617&respCode=000000&respMsg=交易成功&totalFee=0.01&validTime=2&signType=RSA&sign=jN3Ha6J9UUIe9M0L/XeexEdaRL9GB6nMV12wNC7LQvTS6V4nKHj4Qzw6M8cNsA9L0Tb3QFT83B0qO3FJnruDrcHKqBLZb4FkoKKN/WiDBuA2UZQjG4+CBejoGJWfpkWSsei9tXUk36TB27lc2ZlYXSEwuuDwM7M9yvlYysc3fjg=';
		////
		$http = Mockery::mock(Client::class);
		$http->shouldReceive('sendRequest')->andReturn(new Response(200, [], $response));
		Pay::set(HttpClientInterface::class, $http);

		$result = Pay::epay()->scan([
			'outTradeNo' => 'YC202406170003',
			'totalFee'   => 0.01,
			'proInfo'    => '充值',
		]);
		self::assertArrayHasKey('payUrl', $result->all());
	}

	public function testQuery()
	{
		$response = 'deviceNo=1234567890&errCode=&errMsg=&field1=2&field2=&field3=&orderNo=20240617144526400259379&orderStatus=2&outTradeNo=YC202406170003&partnerId=6a13eab71c4f4b0aa4757eda6fc59710&respBizDate=20240617&respCode=000000&respMsg=交易成功&totalFee=0.01&signType=RSA&sign=fRMelfVITd0+aV+I5MT9SyTwRjWB+vOVyES9s3l+eKFV9bXwtQLpaORFpr1emepm2mZjCAgK9AORaYYn9vhk+0x+b2jk2QiyQ3aXYrrYx0+foK/OqN9dcJjSTIIpUUitGYk/6CJwe0OFPsDWgqDiLb9A298VFXg++czErz0stcM=';
		$http = Mockery::mock(Client::class);
		$http->shouldReceive('sendRequest')->andReturn(new Response(200, [], $response));
		Pay::set(HttpClientInterface::class, $http);

		$outTradeNo = 'YC202406170003';
		$result = Pay::epay()->query([
			'outTradeNo' => $outTradeNo,
		]);

		self::assertArrayHasKey('orderNo', $result->all());
		self::assertArrayHasKey('orderStatus', $result->all());
		self::assertEquals($outTradeNo, $result->get('outTradeNo'));
	}

	public function testRefundErrorRefundAmt()
	{
		//单元测试检测异常是否正确
		$this->expectException(InvalidResponseException::class);
		$response = 'field1=&field2=&field3=&partnerId=6a13eab71c4f4b0aa4757eda6fc59710&respBizDate=20240617&respCode=027111&respMsg=退款金额错误&signType=RSA&sign=hzreuJgx+iYz71W8HKyQEH4+XqE7c2Ad6NLa8lSJcVEmjc2nZPZl2s+mBVmZX3PYSlysqq5rlXysGMzQxf4CWkuoUK9wGsCDUIssCBOPcRjdsC2/uFaLavs/jagkKE/tLt45D6h4kibgaHIZabN5NgUkP0p0TAFHISsPPdqjKLY=';
		$http = Mockery::mock(Client::class);
		$http->shouldReceive('sendRequest')->andReturn(new Response(200, [], $response));
		Pay::set(HttpClientInterface::class, $http);
		$outTradeNo = 'YC202406170003';
		Pay::epay()->refund([
			'outTradeNo' => $outTradeNo,
			'refundAmt' => 0.02,
		]);
	}

	public function testRefund()
	{
		//单元测试检测异常是否正确
		$response = 'fee=0&field1=2&field2=&field3=&orderStatus=3&outRefundNo=RK-YC202406170004&partnerId=6a13eab71c4f4b0aa4757eda6fc59710&refundAmt=0.01&refundNo=20240617151229401202589&respBizDate=20240617&respCode=000000&respMsg=交易成功&signType=RSA&sign=VJi8vD3ZkcPZXkgkJ3RX9oREKxeNoUAi9+SZoiBHNlNc87QN0NRngmLthHbzJUV6Fz8hQX5jumZQnhTpEqlTgEZsHRCIm7ZsqhingBNVKItq/sAqzlpIeogU/jVE4zueqInYIMbVOj6+3AQyZ1+Tblz6d0JrGals3exmBUt/03U=';
		$http = Mockery::mock(Client::class);
		$http->shouldReceive('sendRequest')->andReturn(new Response(200, [], $response));
		Pay::set(HttpClientInterface::class, $http);

		$outRefundNo = 'RK-YC202406170004';
		$result = Pay::epay()->refund([
			'outTradeNo' => 'YC202406170004',
			'refundAmt' => 0.01,
			'outRefundNo' => $outRefundNo,
		]);

		self::assertArrayHasKey('orderStatus', $result->all());
		self::assertEquals($outRefundNo, $result->get('outRefundNo'));
	}


	public function testCancel()
	{
		$this->expectException(InvalidParamsException::class);

		Pay::epay()->cancel([
			'outTradeNo' => 'YC202406170003',
		]);
	}

	public function testClose()
	{
		$this->expectException(InvalidParamsException::class);

		Pay::epay()->cancel([
			'outTradeNo' => 'YC202406170003',
		]);
	}

	public function testCallback()
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
		$result = Pay::epay()->callback($payload);
		self::assertNotEmpty($result->all());
		self::assertArrayHasKey('outTradeNo', $result->all());
		self::assertArrayHasKey('orderNo', $result->all());
		self::assertArrayHasKey('orderStatus', $result->all());
		self::assertArrayHasKey('field3', $result->all());

	}

    public function testSuccess()
    {
        $result = Pay::epay()->success();

        self::assertInstanceOf(ResponseInterface::class, $result);
        self::assertStringContainsString('success', (string) $result->getBody());
    }
}
