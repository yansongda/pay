<?php

namespace Yansongda\Pay\Tests\Plugin\Jsb;

use GuzzleHttp\Psr7\Response;
use Yansongda\Artful\Contract\ConfigInterface;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Jsb\VerifySignaturePlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Config;

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

	public function testMulconnectorSign()
	{
		$body = 'partnerId=6a13eab71c4f4b0aa4757eda6fc59710&field1=&field2=&field3=&respBizDate=20240618&respCode=000000&respMsg=交易成功&totalRows=17&pageSize=20&currentPage=1&totalPages=1&hasNext=0&hasPrevious=0&signType=RSA&sign=alEzu2R3ZquH9ff0bI4b9Cl4MDDnEM3vPtMGwfwAVYJuYuecCFCf36glEiHu+KHxG/kzyRnDpakmeSPoGfs2GLsSxzxU6p3pNdBesbkZvU8j2WVUdwq1DZ6Z6SDqS1ZEMiRBGymePOGbBows+/DY8RrTplx4j0TvKqCUrfJun4o=&-&transDate=20240617&transTime=20240617151229&orderNo=20240617151229401202589&outTradeNo=RK-YC202406170004&amount=0.01&orderStatus=1&orderType=2&checkStatus=&tradeType=2&extfld1=0.0|0.0&extfld2=&extfld3=&deviceNo=1234567890&operatorId=&payId=oUpF8uLdyMJiT0t792_LFbuv1Lz8&-&transDate=20240617&transTime=20240617150911&orderNo=20240617150911400947909&outTradeNo=YC202406170004&amount=0.01&orderStatus=1&orderType=1&checkStatus=&tradeType=2&extfld1=0.0|0.01&extfld2=元仓充值&extfld3=20240617150917&deviceNo=&operatorId=&payId=oUpF8uLdyMJiT0t792_LFbuv1Lz8&-&transDate=20240613&transTime=20240613164332&orderNo=20240613164332401166829&outTradeNo=RC240613164222066849&amount=0.03&orderStatus=1&orderType=1&checkStatus=&tradeType=2&extfld1=0.0|0.0&extfld2=YC002充值&extfld3=20240613164337&deviceNo=&operatorId=&payId=oUpF8uLdyMJiT0t792_LFbuv1Lz8&-&transDate=20240613&transTime=20240613164134&orderNo=20240613164134400800219&outTradeNo=RC240613164110030316&amount=0.02&orderStatus=1&orderType=1&checkStatus=&tradeType=2&extfld1=0.0|0.0&extfld2=YC002充值&extfld3=20240613164139&deviceNo=&operatorId=&payId=oUpF8uLdyMJiT0t792_LFbuv1Lz8&-&transDate=20240613&transTime=20240613161540&orderNo=20240613161540418461859&outTradeNo=RC240613161530097918&amount=0.01&orderStatus=1&orderType=1&checkStatus=&tradeType=2&extfld1=0.0|0.0&extfld2=YC002充值&extfld3=20240613161551&deviceNo=&operatorId=&payId=oUpF8uLdyMJiT0t792_LFbuv1Lz8&-&transDate=20240613&transTime=20240613153839&orderNo=20240613153839416172119&outTradeNo=RC240613153823029572&amount=0.01&orderStatus=1&orderType=1&checkStatus=&tradeType=2&extfld1=0.0|0.0&extfld2=YC002充值&extfld3=20240613153847&deviceNo=&operatorId=&payId=oUpF8uLdyMJiT0t792_LFbuv1Lz8&-&transDate=20240613&transTime=20240613145133&orderNo=20240613145133412731519&outTradeNo=YC202406132022&amount=0.01&orderStatus=1&orderType=1&checkStatus=&tradeType=2&extfld1=0.0|0.0&extfld2=test充值&extfld3=20240613145138&deviceNo=&operatorId=&payId=oUpF8uLdyMJiT0t792_LFbuv1Lz8&-&transDate=20240613&transTime=20240613144454&orderNo=20240613144454412182359&outTradeNo=TK-YC202406132021&amount=0.01&orderStatus=1&orderType=2&checkStatus=&tradeType=2&extfld1=0.0|0.0&extfld2=&extfld3=&deviceNo=1234567890&operatorId=&payId=oUpF8uLdyMJiT0t792_LFbuv1Lz8&-&transDate=20240613&transTime=20240613144333&orderNo=20240613144333411961589&outTradeNo=YC202406132021&amount=0.01&orderStatus=1&orderType=1&checkStatus=&tradeType=2&extfld1=0.0|0.01&extfld2=test充值&extfld3=20240613144349&deviceNo=&operatorId=&payId=oUpF8uLdyMJiT0t792_LFbuv1Lz8&-&transDate=20240613&transTime=20240613144016&orderNo=20240613144016411383399&outTradeNo=TK-RC240613142104066624&amount=0.01&orderStatus=1&orderType=2&checkStatus=&tradeType=2&extfld1=0.0|0.0&extfld2=&extfld3=&deviceNo=1234567890&operatorId=&payId=oUpF8uLdyMJiT0t792_LFbuv1Lz8&-&transDate=20240613&transTime=20240613144004&orderNo=20240613144004411355039&outTradeNo=TK-RC240613142846088651&amount=0.01&orderStatus=1&orderType=2&checkStatus=&tradeType=2&extfld1=0.0|0.0&extfld2=&extfld3=&deviceNo=1234567890&operatorId=&payId=oUpF8uLdyMJiT0t792_LFbuv1Lz8&-&transDate=20240613&transTime=20240613143945&orderNo=20240613143945411253709&outTradeNo=TK-RC240613143227055276&amount=0.01&orderStatus=1&orderType=2&checkStatus=&tradeType=2&extfld1=0.0|0.0&extfld2=&extfld3=&deviceNo=1234567890&operatorId=&payId=oUpF8uLdyMJiT0t792_LFbuv1Lz8&-&transDate=20240613&transTime=20240613143317&orderNo=20240613143317410648749&outTradeNo=RC240613143227055276&amount=0.01&orderStatus=1&orderType=1&checkStatus=&tradeType=2&extfld1=0.0|0.01&extfld2=YC002充值&extfld3=20240613143338&deviceNo=&operatorId=&payId=oUpF8uLdyMJiT0t792_LFbuv1Lz8&-&transDate=20240613&transTime=20240613142859&orderNo=20240613142859410207589&outTradeNo=RC240613142846088651&amount=0.01&orderStatus=1&orderType=1&checkStatus=&tradeType=2&extfld1=0.0|0.01&extfld2=YC002充值&extfld3=20240613142905&deviceNo=&operatorId=&payId=oUpF8uLdyMJiT0t792_LFbuv1Lz8&-&transDate=20240613&transTime=20240613142143&orderNo=20240613142143409559519&outTradeNo=RC240613142104066624&amount=0.01&orderStatus=1&orderType=1&checkStatus=&tradeType=2&extfld1=0.0|0.01&extfld2=YC002充值&extfld3=20240613142149&deviceNo=&operatorId=&payId=oUpF8uLdyMJiT0t792_LFbuv1Lz8&-&transDate=20240613&transTime=20240613105059&orderNo=20240613105059400587329&outTradeNo=RT-YC202406130102&amount=0.01&orderStatus=1&orderType=2&checkStatus=&tradeType=2&extfld1=0.0|0.0&extfld2=&extfld3=&deviceNo=1234567890&operatorId=&payId=oUpF8uLdyMJiT0t792_LFbuv1Lz8&-&transDate=20240613&transTime=20240613101235&orderNo=20240613101235476739849&outTradeNo=YC202406130102&amount=0.01&orderStatus=1&orderType=1&checkStatus=&tradeType=2&extfld1=0.0|0.01&extfld2=元仓充值&extfld3=20240613101243&deviceNo=&operatorId=&payId=oUpF8uLdyMJiT0t792_LFbuv1Lz8';
		$rocket = (new Rocket())->setDestinationOrigin(new Response(200, [], $body));

		$result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
		self::assertSame($rocket, $result);
	}


	public function testEmptySign()
	{
		self::expectException(InvalidSignException::class);
		self::expectExceptionCode(Exception::SIGN_EMPTY);

		$body = 'errCode=1&field1=&field2=&field3=&orderNo=20240617144526400259379&orderStatus=1&outTradeNo=YC202406170003&partnerId=6a13eab71c4f4b0aa4757eda6fc59710&payUrl=http://weixintest.jsbchina.cn/epcs/qr/login.htm?qrCode=2018060611052793473720240617144526688568&respBizDate=20240617&respCode=000000&respMsg=交易成功&totalFee=0.01&validTime=2';

		$rocket = (new Rocket())->setDestinationOrigin(new Response(200, [], $body));

		$result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
		self::assertSame($rocket, $result);
	}

	public function testSignWrong()
	{
		self::expectException(InvalidSignException::class);
		self::expectExceptionCode(Exception::SIGN_ERROR);
		$body = 'errCode=1&field1=&field2=&field3=&orderNo=20240617144526400259379&orderStatus=1&outTradeNo=YC202406170003&partnerId=6a13eab71c4f4b0aa4757eda6fc59710&payUrl=http://weixintest.jsbchina.cn/epcs/qr/login.htm?qrCode=2018060611052793473720240617144526688568&respBizDate=20240617&respCode=000000&respMsg=交易成功&totalFee=0.01&validTime=2&signType=RSA&sign=jN3Ha6J9UUIe9M0L/XeexEdaRL9GB6nMV12wNC7LQvTS6V4nKHj4Qzw6M8cNsA9L0Tb3QFT83B0qO3FJnruDrcHKqBLZb4FkoKKN/WiDBuA2UZQjG4+CBejoGJWfpkWSsei9tXUk36TB27lc2ZlYXSEwuuDwM7M9yvlYysc3fjg=';

		$rocket = (new Rocket())->setDestinationOrigin(new Response(200, [], $body));

		$result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
		self::assertSame($rocket, $result);
	}

	public function testMissingEpayPublicCertPath()
	{
		$body = 'errCode=1&field1=&field2=&field3=&orderNo=20240617144526400259379&orderStatus=1&outTradeNo=YC202406170003&partnerId=6a13eab71c4f4b0aa4757eda6fc59710&payUrl=http://weixintest.jsbchina.cn/epcs/qr/login.htm?qrCode=2018060611052793473720240617144526688568&respBizDate=20240617&respCode=000000&respMsg=交易成功&totalFee=0.01&validTime=2&signType=RSA&sign=jN3Ha6J9UUIe9M0L/XeexEdaRL9GB6nMV12wNC7LQvTS6V4nKHj4Qzw6M8cNsA9L0Tb3QFT83B0qO3FJnruDrcHKqBLZb4FkoKKN/WiDBuA2UZQjG4+CBejoGJWfpkWSsei9tXUk36TB27lc2ZlYXSEwuuDwM7M9yvlYysc3fjg=';

		$rocket = (new Rocket())->setDestinationOrigin(new Response(200, [], $body));
		Pay::set(ConfigInterface::class, new Config());
		self::expectException(InvalidConfigException::class);
		self::expectExceptionCode(Exception::CONFIG_JSB_INVALID);
		self::expectExceptionMessage('配置异常: 缺少配置参数 -- [jsb_public_cert_path]');

		$result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
		self::assertSame($rocket, $result);
	}

}
