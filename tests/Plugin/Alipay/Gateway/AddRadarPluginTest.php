<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\Gateway;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\Gateway\AddRadarPlugin;
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
        $result = $this->plugin->assembly((new Rocket())
            ->setParams([])
            ->setPayload(new Collection(['name' => 'yansongda'])), fn ($rocket) => $rocket);

        self::assertEquals('https://openapi.alipay.com/gateway.do?charset=utf-8', (string) $result->getRadar()->getUri());
        self::assertStringContainsString('name=yansongda', (string) $result->getRadar()->getBody());
        self::assertEquals('POST', $result->getRadar()->getMethod());
    }

    public function testRadarHeaders(): void
    {
        $result = $this->plugin->assembly((new Rocket())
            ->setParams([])
            ->setPayload(new Collection(['name' => 'yansongda'])), fn ($rocket) => $rocket);

        self::assertEquals('application/x-www-form-urlencoded', $result->getRadar()->getHeaderLine('Content-Type'));
        self::assertEquals('yansongda/pay-v3', $result->getRadar()->getHeaderLine('User-Agent'));
    }
}
