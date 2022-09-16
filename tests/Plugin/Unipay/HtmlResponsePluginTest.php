<?php

namespace Yansongda\Pay\Tests\Plugin\Unipay;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Pay\Plugin\Unipay\HtmlResponsePlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class HtmlResponsePluginTest extends TestCase
{
    protected $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new HtmlResponsePlugin();
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
}
