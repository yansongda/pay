<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Bestpay;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Bestpay\V1\AddPayloadSignPlugin;
use Yansongda\Pay\Plugin\Bestpay\V1\StartPlugin;
use Yansongda\Pay\Tests\TestCase;

class AddPayloadSignPluginTest extends TestCase
{
    public function testAssembly(): void
    {
        $rocket = new Rocket();
        $rocket->setParams([
            'outTradeNo' => 'ORDER001',
            'tradeAmt' => '99',
            'subject' => '测试',
        ]);

        $rocket = (new StartPlugin())->assembly($rocket, fn ($r) => $r);
        $rocket->mergePayload(['_path' => '/pay/tradeCreate']);

        $rocket = (new AddPayloadSignPlugin())->assembly($rocket, fn ($r) => $r);

        $payload = $rocket->getPayload();

        self::assertEquals('/pay/tradeCreate', $payload->get('path'));
        self::assertNotEmpty($payload->get('sign'));
        self::assertNotEmpty($payload->get('commonParams'));
        self::assertNotEmpty($payload->get('bizContent'));

        $biz = json_decode((string) $payload->get('bizContent'), true);
        self::assertEquals('ORDER001', $biz['outTradeNo']);
        self::assertEquals('99', $biz['tradeAmt']);
        self::assertEquals('3178033925245778', $biz['merchantNo']);
        self::assertArrayNotHasKey('institutionType', $biz);
        self::assertArrayNotHasKey('institutionCode', $biz);
        self::assertArrayNotHasKey('_path', $biz);

        $common = json_decode((string) $payload->get('commonParams'), true);
        self::assertEquals('MERCHANT', $common['institutionType']);
        self::assertEquals('3178033925245778', $common['institutionCode']);
    }

    public function testAssemblyFiltersNullFieldsFromBizContent(): void
    {
        $rocket = new Rocket();
        $rocket->setParams([
            'outTradeNo' => 'ORDER001',
            'storeCode' => null,
            'remark' => '',
        ]);

        $rocket = (new StartPlugin())->assembly($rocket, fn ($r) => $r);
        $rocket->mergePayload(['_path' => '/pay/tradeCreate', 'notifyUrl' => null]);

        $rocket = (new AddPayloadSignPlugin())->assembly($rocket, fn ($r) => $r);

        $biz = json_decode((string) $rocket->getPayload()->get('bizContent'), true);

        // null 字段不进入 bizContent（对齐官方 fastjson 默认不序列化 null）
        self::assertArrayNotHasKey('storeCode', $biz);
        self::assertArrayNotHasKey('notifyUrl', $biz);
        // 空字符串保留（官方语义：签名拼为 k=，非 null）
        self::assertSame('', $biz['remark']);
    }

    public function testAssemblyBizContentDoesNotEscapeSlash(): void
    {
        // 对齐官方 fastjson 默认序列化：URL 中的 `/` 不转义为 `\/`（JSON_UNESCAPED_SLASHES）
        $rocket = new Rocket();
        $rocket->setParams([
            'outTradeNo' => 'ORDER001',
            'notifyUrl' => 'https://pay.yansongda.cn/bestpay/notify',
            'subject' => '测试',
        ]);

        $rocket = (new StartPlugin())->assembly($rocket, fn ($r) => $r);
        $rocket->mergePayload(['_path' => '/pay/tradeCreate']);

        $rocket = (new AddPayloadSignPlugin())->assembly($rocket, fn ($r) => $r);

        $bizContent = (string) $rocket->getPayload()->get('bizContent');

        self::assertStringContainsString('"notifyUrl":"https://pay.yansongda.cn/bestpay/notify"', $bizContent);
        self::assertStringNotContainsString('\\/', $bizContent);
        self::assertStringContainsString('"subject":"测试"', $bizContent);
    }

    public function testAssemblyThrowsWhenPathMissing(): void
    {
        $this->expectException(InvalidParamsException::class);
        $this->expectExceptionCode(Exception::PARAMS_BESTPAY_PATH_MISSING);

        $rocket = new Rocket();
        $rocket->setParams(['outTradeNo' => 'ORDER001']);

        $rocket = (new StartPlugin())->assembly($rocket, fn ($r) => $r);

        (new AddPayloadSignPlugin())->assembly($rocket, fn ($r) => $r);
    }
}
