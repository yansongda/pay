<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\Fund\Transfer;

use GuzzleHttp\Psr7\Uri;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Parser\OriginResponseParser;
use Yansongda\Pay\Plugin\Wechat\Fund\Transfer\DownloadReceiptPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class DownloadReceiptPluginTest extends TestCase
{
    /**
     * @var \Yansongda\Pay\Plugin\Wechat\Fund\Transfer\DownloadReceiptPlugin
     */
    protected $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new DownloadReceiptPlugin();
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection(['download_url' => 'https://yansongda.cn']));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $radar = $result->getRadar();

        self::assertEquals(OriginResponseParser::class, $result->getDirection());
        self::assertEquals('GET', $radar->getMethod());
        self::assertEquals(new Uri('https://yansongda.cn'), $radar->getUri());
    }

    public function testNormalNoDownloadUrl()
    {
        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection());

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::MISSING_NECESSARY_PARAMS);

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }
}
