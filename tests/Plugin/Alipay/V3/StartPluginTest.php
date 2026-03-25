<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3;

use Yansongda\Artful\Contract\ConfigInterface;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Alipay\V3\StartPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Config;

class StartPluginTest extends TestCase
{
    protected StartPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new StartPlugin();
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'v3']);

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
        $payload = $result->getPayload();

        self::assertEquals('9021000122682882', $payload->get('app_id'));
        self::assertEquals('POST', $payload->get('_method'));
        self::assertEquals('application/json', $payload->get('_headers')['Accept']);
        self::assertEquals('yansongda/pay-v3', $payload->get('_headers')['User-Agent']);
    }

    public function testAppAuthToken()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'v3', '_app_auth_token' => 'test_token']);

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertEquals('test_token', $result->getPayload()->get('app_auth_token'));
    }

    public function testMissingAppPublicCertPath()
    {
        Pay::set(ConfigInterface::class, new Config());

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionCode(Exception::CONFIG_ALIPAY_INVALID);

        $this->plugin->assembly(new Rocket(), fn ($rocket) => $rocket);
    }
}
