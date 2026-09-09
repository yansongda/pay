<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Wechat\Openapi\Oauth;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\Openapi\Oauth\UserInfoPlugin;
use Yansongda\Pay\Tests\TestCase;

class UserInfoPluginTest extends TestCase
{
    protected UserInfoPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new UserInfoPlugin();
    }

    public function testNormal(): void
    {
        $rocket = new Rocket();
        $rocket->setParams([
            'access_token' => 'the_wechat_access_token',
            'openid' => 'the_wechat_openid',
            'lang' => 'en',
        ]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        self::assertEquals('GET', $payload->get('_method'));
        self::assertEquals('/sns/userinfo?'.http_build_query([
            'access_token' => 'the_wechat_access_token',
            'openid' => 'the_wechat_openid',
            'lang' => 'en',
        ]), $payload->get('_url'));
        self::assertEquals('', $payload->get('_body'));
    }

    public function testDefaultLang(): void
    {
        $rocket = new Rocket();
        $rocket->setParams([
            'access_token' => 'the_wechat_access_token',
            'openid' => 'the_wechat_openid',
        ]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        self::assertEquals('GET', $payload->get('_method'));
        self::assertEquals('/sns/userinfo?'.http_build_query([
            'access_token' => 'the_wechat_access_token',
            'openid' => 'the_wechat_openid',
            'lang' => 'zh_CN',
        ]), $payload->get('_url'));
        self::assertStringContainsString('lang=zh_CN', $payload->get('_url'));
        self::assertEquals('', $payload->get('_body'));
    }

    public function testMissingAccessTokenThrows(): void
    {
        $rocket = new Rocket();
        $rocket->setParams(['openid' => 'the_wechat_openid']);

        $this->expectException(InvalidParamsException::class);
        $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        $this->expectExceptionMessage('参数异常: 缺少微信网页授权参数 -- [access_token]');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testMissingOpenidThrows(): void
    {
        $rocket = new Rocket();
        $rocket->setParams(['access_token' => 'the_wechat_access_token']);

        $this->expectException(InvalidParamsException::class);
        $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        $this->expectExceptionMessage('参数异常: 缺少微信网页授权参数 -- [openid]');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }
}
