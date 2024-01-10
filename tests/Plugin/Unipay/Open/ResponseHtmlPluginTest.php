<?php

namespace Yansongda\Pay\Tests\Plugin\Unipay\Open;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Pay\Plugin\Unipay\Open\ResponseHtmlPlugin;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class ResponseHtmlPluginTest extends TestCase
{
    protected ResponseHtmlPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new ResponseHtmlPlugin();
    }

    public function testHtml()
    {
        $rocket = new Rocket();
        $rocket->setRadar(new Request('POST', 'https://yansongda.cn'))
            ->setPayload(new Collection(['name' => 'yansongda']));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $contents = (string) $result->getDestination()->getBody();

        self::assertInstanceOf(ResponseInterface::class, $result->getDestination());
        self::assertStringContainsString('pay_form', $contents);
        self::assertStringContainsString('yansongda', $contents);
    }

    public function testPayloadUnderline()
    {
        $rocket = new Rocket();
        $rocket->setRadar(new Request('POST', 'https://yansongda.cn'))
            ->setPayload(new Collection(['name' => 'yansongda', '_age' => 'aaaa']));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $contents = (string) $result->getDestination()->getBody();

        self::assertInstanceOf(ResponseInterface::class, $result->getDestination());
        self::assertStringContainsString('pay_form', $contents);
        self::assertStringContainsString('yansongda', $contents);
        self::assertStringNotContainsString('_age', $contents);
    }
}
