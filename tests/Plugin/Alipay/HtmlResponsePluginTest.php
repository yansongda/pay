<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay;

use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Pay\Plugin\Alipay\HtmlResponsePlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;

class HtmlResponsePluginTest extends TestCase
{
    public function testRedirect()
    {
        $rocket = new Rocket();
        $rocket->setRadar(new Request('GET', 'https://yansongda.cn'))
                ->setPayload(new Collection(['name' => 'yansongda']));

        $plugin = new HtmlResponsePlugin();
        $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertInstanceOf(ResponseInterface::class, $result->getDestination());
        self::assertArrayHasKey('Location', $result->getDestination()->getHeaders());
        self::assertEquals('https://yansongda.cn?name=yansongda', $result->getDestination()->getHeaderLine('Location'));
    }

    public function testHtml()
    {
        $rocket = new Rocket();
        $rocket->setRadar(new Request('POST', 'https://yansongda.cn'))
            ->setPayload(new Collection(['name' => 'yansongda']));

        $plugin = new HtmlResponsePlugin();
        $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $contents = $result->getDestination()->getBody()->getContents();

        self::assertInstanceOf(ResponseInterface::class, $result->getDestination());
        self::assertStringContainsString('alipay_submit', $contents);
        self::assertStringContainsString('yansongda', $contents);
    }
}
