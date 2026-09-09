<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Wechat\Openapi\Oauth;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\Openapi\Oauth\WebAccessTokenPlugin;
use Yansongda\Pay\Tests\TestCase;

class WebAccessTokenPluginTest extends TestCase
{
    protected WebAccessTokenPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new WebAccessTokenPlugin();
    }

    public function testNormal(): void
    {
        $rocket = new Rocket();
        $rocket->setParams([
            'appid' => 'wx55955316af4ef13',
            'secret' => 'my_mp_app_secret',
            'code' => 'the_wechat_oauth_code',
        ]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        self::assertEquals('GET', $payload->get('_method'));
        self::assertEquals('/sns/oauth2/access_token?'.http_build_query([
            'appid' => 'wx55955316af4ef13',
            'secret' => 'my_mp_app_secret',
            'code' => 'the_wechat_oauth_code',
            'grant_type' => 'authorization_code',
        ]), $payload->get('_url'));
        self::assertEquals('', $payload->get('_body'));
    }

    public function testWithConfigDefaults(): void
    {
        $rocket = new Rocket();
        $rocket->setParams(['code' => 'the_wechat_oauth_code']);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        self::assertEquals('GET', $payload->get('_method'));
        self::assertEquals('/sns/oauth2/access_token?'.http_build_query([
            'appid' => 'wx55955316af4ef13',
            'secret' => 'mp_app_secret_for_test',
            'code' => 'the_wechat_oauth_code',
            'grant_type' => 'authorization_code',
        ]), $payload->get('_url'));
        self::assertEquals('', $payload->get('_body'));
    }

    public function testMissingCodeThrows(): void
    {
        $rocket = new Rocket();
        $rocket->setParams([]);

        $this->expectException(InvalidParamsException::class);
        $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        $this->expectExceptionMessage('参数异常: 缺少微信网页授权参数 -- [code]');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testMissingConfigSecretThrows(): void
    {
        $rocket = new Rocket();
        $rocket->setParams([
            '_config' => 'service_provider',
            'code' => 'the_wechat_oauth_code',
        ]);

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionCode(Exception::CONFIG_WECHAT_INVALID);
        $this->expectExceptionMessage('配置异常: 缺少微信配置 -- [mp_app_secret] 或 [mp_app_id]');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }
}
