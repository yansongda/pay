<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Wechat\Openapi\Oauth;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\Openapi\Oauth\Code2SessionPlugin;
use Yansongda\Pay\Tests\TestCase;

class Code2SessionPluginTest extends TestCase
{
    protected Code2SessionPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new Code2SessionPlugin();
    }

    public function testNormal(): void
    {
        $rocket = new Rocket();
        $rocket->setParams(['appid' => 'wx1234567890', 'secret' => 'test_secret', 'js_code' => 'test_js_code']);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        self::assertEquals('GET', $payload->get('_method'));
        self::assertEquals('/sns/jscode2session?appid=wx1234567890&secret=test_secret&js_code=test_js_code&grant_type=authorization_code', $payload->get('_url'));
        self::assertEquals('', $payload->get('_body'));
    }

    public function testWithConfigDefaults(): void
    {
        $rocket = new Rocket();
        $rocket->setParams(['js_code' => 'test_js_code']);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        self::assertEquals('GET', $payload->get('_method'));
        self::assertStringContainsString('appid=wx55955316af4ef14', $payload->get('_url'));
        self::assertStringContainsString('secret=mini_app_secret_for_test', $payload->get('_url'));
        self::assertStringContainsString('js_code=test_js_code', $payload->get('_url'));
        self::assertStringContainsString('grant_type=authorization_code', $payload->get('_url'));
    }

    public function testMissingJsCodeThrows(): void
    {
        $rocket = new Rocket();
        $rocket->setParams(['appid' => 'wx1234567890', 'secret' => 'test_secret']);

        $this->expectException(InvalidParamsException::class);
        $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testParamsSecretOverridesConfig(): void
    {
        $rocket = new Rocket();
        $rocket->setParams(['appid' => 'wx1234567890', 'secret' => 'params_secret_override', 'js_code' => 'test_js_code']);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        self::assertStringContainsString('secret=params_secret_override', $payload->get('_url'));
        self::assertStringNotContainsString('mini_app_secret_for_test', $payload->get('_url'));
    }
}
