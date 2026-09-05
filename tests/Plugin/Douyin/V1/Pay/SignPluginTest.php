<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Douyin\V1\Pay;

use Yansongda\Artful\Direction\NoHttpRequestDirection;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Douyin\V1\Pay\SignPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class SignPluginTest extends TestCase
{
    protected SignPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new SignPlugin();
    }

    public function testNormal(): void
    {
        $fields = [
            'outOrderNo' => '20260905123456',
            'totalAmount' => 100,
            'skuList' => [
                ['skuId' => 'sku_001', 'count' => 1, 'price' => 100],
            ],
            'orderEntrySchema' => ['path' => 'pages/index/index'],
        ];

        $rocket = new Rocket();
        $rocket->setParams([]);
        $rocket->setPayload(new Collection($fields));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        // 不发送 HTTP 请求
        self::assertSame(NoHttpRequestDirection::class, $result->getDirection());

        $destination = $result->getDestination();
        self::assertInstanceOf(Collection::class, $destination);

        $data = $destination->get('data');
        self::assertIsString($data);
        self::assertSame($data, json_encode($fields, JSON_UNESCAPED_UNICODE));
        self::assertSame($fields, json_decode($data, true));

        $byteAuthorization = $destination->get('byteAuthorization');
        self::assertIsString($byteAuthorization);
        self::assertStringStartsWith('SHA256-RSA2048 ', $byteAuthorization);
        self::assertStringContainsString('appid="tt226e54d3bd581bf801"', $byteAuthorization);
        self::assertStringContainsString('key_version=1', $byteAuthorization);
    }

    public function testVerifySignatureFromOutput(): void
    {
        $rocket = new Rocket();
        $rocket->setParams([]);
        $rocket->setPayload(new Collection([
            'outOrderNo' => '20260905123456',
            'totalAmount' => 100,
        ]));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        $destination = $result->getDestination();
        self::assertInstanceOf(Collection::class, $destination);

        $data = $destination->get('data');
        $byteAuthorization = $destination->get('byteAuthorization');
        self::assertIsString($data);
        self::assertIsString($byteAuthorization);

        // 从输出 byteAuthorization 反解 nonce_str/timestamp/signature（插件无注入通道）
        self::assertSame(1, preg_match(
            '/nonce_str="([^"]+)",timestamp="([^"]+)",key_version=1,signature="([^"]+)"/',
            $byteAuthorization,
            $matches
        ));

        [, $nonce, $timestamp, $signature] = $matches;

        $contents = "POST\n/requestOrder\n".$timestamp."\n".$nonce."\n".$data."\n";
        $publicKey = openssl_pkey_get_public(file_get_contents(__DIR__.'/../../../../Cert/douyinAppPublicKey.pem'));

        self::assertNotFalse($publicKey);
        self::assertSame(1, openssl_verify($contents, base64_decode($signature), $publicKey, OPENSSL_ALGO_SHA256));
    }

    public function testUnderscoreKeysExcluded(): void
    {
        $rocket = new Rocket();
        $rocket->setParams([]);
        $rocket->setPayload(new Collection([
            '_foo' => 'bar',
            'outOrderNo' => '20260905123456',
        ]));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        $destination = $result->getDestination();
        self::assertInstanceOf(Collection::class, $destination);

        $data = $destination->get('data');
        self::assertIsString($data);
        self::assertSame(['outOrderNo' => '20260905123456'], json_decode($data, true));
    }

    public function testEmptyPayload(): void
    {
        $rocket = new Rocket();
        $rocket->setParams([]);

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);

        $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
    }

    public function testOnlyUnderscorePayload(): void
    {
        $rocket = new Rocket();
        $rocket->setParams([]);
        $rocket->setPayload(new Collection(['_method' => 'POST', '_url' => '/requestOrder']));

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);

        $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
    }
}
