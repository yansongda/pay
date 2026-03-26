<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\V2;

use Yansongda\Pay\Plugin\Alipay\V2\AddRadarPlugin;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

/**
 * @internal
 *
 * @coversNothing
 */
class AddRadarPluginTest extends TestCase
{
    protected AddRadarPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new AddRadarPlugin();
    }

    public function testRadarPostNormal(): void
    {
        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection(['name' => 'yansongda']));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertEquals('https://openapi.alipay.com/gateway.do?charset=utf-8', (string) $result->getRadar()->getUri());
        self::stringContains('name=yansongda', (string) $result->getRadar()->getBody());
        self::assertEquals('POST', $result->getRadar()->getMethod());
    }

    public function testRadarGetNormal(): void
    {
        $rocket = new Rocket();
        $rocket->setParams(['_method' => 'get'])->setPayload(new Collection(['name' => 'yansongda']));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertEquals('https://openapi.alipay.com/gateway.do?charset=utf-8', (string) $result->getRadar()->getUri());
        self::assertEquals('GET', $result->getRadar()->getMethod());
    }

    public function testRadarHeaders(): void
    {
        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection(['name' => 'yansongda']));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertEquals('application/x-www-form-urlencoded', $result->getRadar()->getHeaderLine('Content-Type'));
        self::assertEquals('yansongda/pay-v3', $result->getRadar()->getHeaderLine('User-Agent'));
    }

    public function testRadarMultipart(): void
    {
        $rocket = new Rocket();
        $rocket->setParams(['_multipart' => [['name' => 'yansongda', 'contents' => 'yansongda']]])->setPayload(new Collection(['name' => 'yansongda']));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertEmpty($result->getRadar()->getHeaderLine('Content-Type'));
    }
}
