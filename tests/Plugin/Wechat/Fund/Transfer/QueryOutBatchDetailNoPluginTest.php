<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\Fund\Transfer;

use GuzzleHttp\Psr7\Uri;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Wechat\Fund\Transfer\QueryOutBatchDetailNoPlugin;
use Yansongda\Pay\Provider\Wechat;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class QueryOutBatchDetailNoPluginTest extends TestCase
{
    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection(['out_batch_no' => '123', 'out_detail_no' => '456']));

        $plugin = new QueryOutBatchDetailNoPlugin();

        $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $radar = $result->getRadar();

        self::assertEquals(new Uri(Wechat::URL[Pay::MODE_NORMAL].'v3/transfer/batches/out-batch-no/123/details/out-detail-no/456'), $radar->getUri());
        self::assertEquals('GET', $radar->getMethod());
    }

    public function testNormalNoBatchId()
    {
        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection(['out_detail_no' => '456']));

        $plugin = new QueryOutBatchDetailNoPlugin();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::MISSING_NECESSARY_PARAMS);

        $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNormalNoDetailId()
    {
        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection(['out_batch_no' => '123']));

        $plugin = new QueryOutBatchDetailNoPlugin();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::MISSING_NECESSARY_PARAMS);

        $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testPartner()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection(['out_batch_no' => '123', 'out_detail_no' => '456']));

        $plugin = new QueryOutBatchDetailNoPlugin();

        $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $radar = $result->getRadar();

        self::assertEquals(new Uri(Wechat::URL[Pay::MODE_NORMAL].'v3/partner-transfer/batches/out-batch-no/123/details/out-detail-no/456'), $radar->getUri());
        self::assertEquals('GET', $radar->getMethod());
    }

    public function testPartnerNoBatchId()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection(['out_detail_no' => '456']));

        $plugin = new QueryOutBatchDetailNoPlugin();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::MISSING_NECESSARY_PARAMS);

        $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testPartnerNoDetailId()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection(['out_batch_no' => '123']));

        $plugin = new QueryOutBatchDetailNoPlugin();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::MISSING_NECESSARY_PARAMS);

        $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }
}
