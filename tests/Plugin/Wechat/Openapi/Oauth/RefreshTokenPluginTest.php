<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Wechat\Openapi\Oauth;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\Openapi\Oauth\RefreshTokenPlugin;
use Yansongda\Pay\Tests\TestCase;

class RefreshTokenPluginTest extends TestCase
{
    protected RefreshTokenPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new RefreshTokenPlugin();
    }

    public function testNormal(): void
    {
        $rocket = new Rocket();
        $rocket->setParams([
            'appid' => 'wx55955316af4ef13',
            'refresh_token' => 'the_wechat_refresh_token',
        ]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        self::assertEquals('GET', $payload->get('_method'));
        self::assertEquals('/sns/oauth2/refresh_token?'.http_build_query([
            'appid' => 'wx55955316af4ef13',
            'grant_type' => 'refresh_token',
            'refresh_token' => 'the_wechat_refresh_token',
        ]), $payload->get('_url'));
        self::assertEquals('', $payload->get('_body'));
    }

    public function testWithConfigDefaults(): void
    {
        $rocket = new Rocket();
        $rocket->setParams(['refresh_token' => 'the_wechat_refresh_token']);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        self::assertEquals('GET', $payload->get('_method'));
        self::assertEquals('/sns/oauth2/refresh_token?'.http_build_query([
            'appid' => 'wx55955316af4ef13',
            'grant_type' => 'refresh_token',
            'refresh_token' => 'the_wechat_refresh_token',
        ]), $payload->get('_url'));
        self::assertEquals('', $payload->get('_body'));
    }

    public function testMissingRefreshTokenThrows(): void
    {
        $rocket = new Rocket();
        $rocket->setParams([]);

        $this->expectException(InvalidParamsException::class);
        $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        $this->expectExceptionMessage('参数异常: 缺少微信网页授权参数 -- [refresh_token]');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testMissingConfigAppIdThrows(): void
    {
        $rocket = new Rocket();
        $rocket->setParams([
            'appid' => '',
            'refresh_token' => 'the_wechat_refresh_token',
        ]);

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionCode(Exception::CONFIG_WECHAT_INVALID);
        $this->expectExceptionMessage('配置异常: 缺少微信配置 -- [mp_app_id]');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }
}
