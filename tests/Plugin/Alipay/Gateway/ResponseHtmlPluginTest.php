<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\Gateway;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\Gateway\ResponseHtmlPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

/**
 * @internal
 *
 * @coversNothing
 */
class ResponseHtmlPluginTest extends TestCase
{
    private ResponseHtmlPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new ResponseHtmlPlugin();
    }

    public function testRedirect(): void
    {
        $rocket = new Rocket();
        $rocket->setRadar(new Request('GET', 'https://yansongda.cn'))
            ->setPayload(new Collection(['name' => 'yansongda']));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertInstanceOf(ResponseInterface::class, $result->getDestination());
        self::assertEquals('https://yansongda.cn?name=yansongda', $result->getDestination()->getHeaderLine('Location'));
    }

    public function testHtml(): void
    {
        $rocket = new Rocket();
        $rocket->setRadar(new Request('POST', 'https://yansongda.cn'))
            ->setPayload(new Collection(['name' => 'yansongda']));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertStringContainsString('alipay_submit', (string) $result->getDestination()->getBody());
    }
}
