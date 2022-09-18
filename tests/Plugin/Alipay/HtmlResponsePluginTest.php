<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Pay\Plugin\Alipay\HtmlResponsePlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class HtmlResponsePluginTest extends TestCase
{
    private $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new HtmlResponsePlugin();
    }

    public function testRedirect()
    {
        $rocket = new Rocket();
        $rocket->setRadar(new Request('GET', 'https://yansongda.cn'))
                ->setPayload(new Collection(['name' => 'yansongda']));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertInstanceOf(ResponseInterface::class, $result->getDestination());
        self::assertArrayHasKey('Location', $result->getDestination()->getHeaders());
        self::assertEquals('https://yansongda.cn?name=yansongda', $result->getDestination()->getHeaderLine('Location'));
    }

    public function testRedirectIncludeMark()
    {
        $rocket = new Rocket();
        $rocket->setRadar(new Request('GET', 'https://yansongda.cn?charset=utf8'))
            ->setPayload(new Collection(['name' => 'yansongda']));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertInstanceOf(ResponseInterface::class, $result->getDestination());
        self::assertArrayHasKey('Location', $result->getDestination()->getHeaders());
        self::assertEquals('https://yansongda.cn?charset=utf8&name=yansongda', $result->getDestination()->getHeaderLine('Location'));
    }

    public function testHtml()
    {
        $rocket = new Rocket();
        $rocket->setRadar(new Request('POST', 'https://yansongda.cn'))
            ->setPayload(new Collection(['name' => 'yansongda']));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $contents = (string) $result->getDestination()->getBody();

        self::assertInstanceOf(ResponseInterface::class, $result->getDestination());
        self::assertStringContainsString('alipay_submit', $contents);
        self::assertStringContainsString('yansongda', $contents);
    }
}
