<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Douyin\V1;

use Yansongda\Artful\Contract\ConfigInterface;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config;
use Yansongda\Pay\Config\DouyinConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Douyin\V1\GetClientTokenPlugin;
use Yansongda\Pay\Tests\TestCase;

class GetClientTokenPluginTest extends TestCase
{
    protected GetClientTokenPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new GetClientTokenPlugin();
    }

    public function testNormal(): void
    {
        $rocket = new Rocket();
        $rocket->setParams([]);

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
        $payload = $result->getPayload();

        self::assertEquals('POST', $payload->get('_method'));
        self::assertEquals('/oauth/client_token/', $payload->get('_url'));
        self::assertEquals('client_credential', $payload->get('grant_type'));
        self::assertEquals('tt226e54d3bd581bf801', $payload->get('client_key'));
        self::assertEquals('douyin_app_secret', $payload->get('client_secret'));
    }

    public function testMissingAppId(): void
    {
        $config = new DouyinConfig([
            'app_id' => 'tt226e54d3bd581bf801',
            'app_secret' => 'douyin_app_secret',
        ]);
        $config->setAppId('');

        Pay::set(ConfigInterface::class, new Config([
            'douyin' => ['default' => $config],
        ]));

        self::expectException(InvalidConfigException::class);
        self::expectExceptionCode(Exception::CONFIG_DOUYIN_INVALID);

        $this->plugin->assembly((new Rocket())->setParams([]), fn ($rocket) => $rocket);
    }

    public function testMissingAppSecret(): void
    {
        $config = new DouyinConfig([
            'app_id' => 'tt226e54d3bd581bf801',
            'app_secret' => 'douyin_app_secret',
        ]);
        $config->setAppSecret('');

        Pay::set(ConfigInterface::class, new Config([
            'douyin' => ['default' => $config],
        ]));

        self::expectException(InvalidConfigException::class);
        self::expectExceptionCode(Exception::CONFIG_DOUYIN_INVALID);

        $this->plugin->assembly((new Rocket())->setParams([]), fn ($rocket) => $rocket);
    }
}
