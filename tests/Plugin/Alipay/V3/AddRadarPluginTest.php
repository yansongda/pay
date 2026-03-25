<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V3\AddRadarPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class AddRadarPluginTest extends TestCase
{
    protected AddRadarPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new AddRadarPlugin();
    }

    public function testRadarPostNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'v3'])
            ->setPayload(new Collection([
                '_method' => 'POST',
                '_url' => '/v3/alipay/trade/query',
                '_body' => ['name' => 'yansongda'],
                '_headers' => ['Accept' => 'application/json'],
            ]));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertEquals('https://openapi.alipay.com/v3/alipay/trade/query', (string) $result->getRadar()->getUri());
        self::assertSame('{"name":"yansongda"}', (string) $result->getRadar()->getBody());
        self::assertEquals('POST', $result->getRadar()->getMethod());
    }

    public function testRadarGetNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'v3'])
            ->setPayload(new Collection([
                '_method' => 'GET',
                '_url' => '/v3/alipay/fund/trans/common/query?out_biz_no=123',
                '_body' => '',
            ]));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertEquals('https://openapi.alipay.com/v3/alipay/fund/trans/common/query?out_biz_no=123', (string) $result->getRadar()->getUri());
        self::assertEquals('GET', $result->getRadar()->getMethod());
        self::assertSame('', (string) $result->getRadar()->getBody());
    }

    public function testRadarHeaders()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'v3'])
            ->setPayload(new Collection([
                '_method' => 'POST',
                '_url' => '/v3/alipay/trade/query',
                '_body' => ['name' => 'yansongda'],
                '_headers' => ['User-Agent' => 'yansongda/pay-v3'],
            ]));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertEquals('application/json', $result->getRadar()->getHeaderLine('Content-Type'));
        self::assertEquals('yansongda/pay-v3', $result->getRadar()->getHeaderLine('User-Agent'));
    }

    public function testRadarMultipart()
    {
        $rocket = new Rocket();
        $rocket->setParams([
            '_config' => 'v3',
            '_multipart' => [
                [
                    'name' => 'file_content',
                    'contents' => 'binary-image',
                    'filename' => 'face.jpg',
                ],
            ],
        ])->setPayload(new Collection([
            '_method' => 'POST',
            '_url' => '/v3/datadigital/fincloud/generalsaas/face/source/certify',
            '_body' => ['cert_name' => 'yansongda'],
            '_headers' => ['Accept' => 'application/json'],
        ]));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertStringStartsWith('multipart/form-data; boundary=', $result->getRadar()->getHeaderLine('Content-Type'));
        self::assertStringContainsString('name="data"', (string) $result->getRadar()->getBody());
        self::assertStringContainsString('{"cert_name":"yansongda"}', (string) $result->getRadar()->getBody());
        self::assertStringContainsString('name="file_content"', (string) $result->getRadar()->getBody());
    }
}
