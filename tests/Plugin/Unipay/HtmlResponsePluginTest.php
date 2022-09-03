<?php

namespace Yansongda\Pay\Tests\Plugin\Unipay;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Pay\Plugin\Alipay\HtmlResponsePlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class HtmlResponsePluginTest extends TestCase
{
    public function testHtml()
    {
        $rocket = new Rocket();
        $rocket->setRadar(new Request('POST', 'https://yansongda.cn'))
            ->setPayload(new Collection(['name' => 'yansongda']));

        $plugin = new HtmlResponsePlugin();
        $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $contents = (string) $result->getDestination()->getBody();

        self::assertInstanceOf(ResponseInterface::class, $result->getDestination());
        self::assertStringContainsString('pay_form', $contents);
        self::assertStringContainsString('yansongda', $contents);
    }
}
