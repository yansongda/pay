<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Wechat\V3\PayScore\Permissions;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Artful\Direction\OriginResponseDirection;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\ResponsePlugin;
use Yansongda\Pay\Plugin\Wechat\V3\PayScore\Permissions\TerminatePlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class TerminatePluginTest extends TestCase
{
    protected TerminatePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new TerminatePlugin();
    }

    public function testEmptyPayload()
    {
        $rocket = new Rocket();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 解除支付分预授权（签约），参数为空');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testServiceIdMissing()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'empty_wechat_public_cert'])->setPayload(new Collection([
            'openid' => 'oXopenid123',
        ]));

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_WECHAT_SERVICE_ID_MISSING);
        self::expectExceptionMessage('参数异常: 缺少支付分服务ID -- [service_id]');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testMissingOpenidAndAuthorizationCode()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'reason' => 'test',
        ]));

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 解除支付分预授权（签约），参数缺少 `openid` 或 `authorization_code`');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testOpenidBranch()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'openid' => 'oXopenid123',
            'appid' => 'wx_payload_override',
            'reason' => 'test',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals(OriginResponseDirection::class, $result->getDirection());
        self::assertEquals('POST', $result->getPayload()->get('_method'));
        self::assertEquals('/v3/payscore/permissions/openid/oXopenid123/terminate', $result->getPayload()->get('_url'));
        self::assertFalse($result->getPayload()->has('openid'));
        self::assertEquals('wx_payload_override', $result->getPayload()->get('appid'));
        self::assertEquals('12345678901234567890123456789012', $result->getPayload()->get('service_id'));
        self::assertEquals('test', $result->getPayload()->get('reason'));
    }

    public function testOpenidBranchAppIdFromConfig()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'openid' => 'oXopenid123',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('/v3/payscore/permissions/openid/oXopenid123/terminate', $result->getPayload()->get('_url'));
        self::assertEquals('wx55955316af4ef13', $result->getPayload()->get('appid'));
        self::assertEquals('12345678901234567890123456789012', $result->getPayload()->get('service_id'));
        self::assertFalse($result->getPayload()->has('openid'));
    }

    public function testAuthorizationCodeBranch()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'authorization_code' => 'AUTH_CODE',
            'reason' => 'test',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals(OriginResponseDirection::class, $result->getDirection());
        self::assertEquals('POST', $result->getPayload()->get('_method'));
        self::assertEquals('/v3/payscore/permissions/authorization-code/AUTH_CODE/terminate', $result->getPayload()->get('_url'));
        self::assertFalse($result->getPayload()->has('authorization_code'));
        self::assertFalse($result->getPayload()->has('appid'));
        self::assertEquals('12345678901234567890123456789012', $result->getPayload()->get('service_id'));
        self::assertEquals('test', $result->getPayload()->get('reason'));
    }

    /**
     * 204 无包体回归断言：TerminatePlugin 两分支均设置 OriginResponseDirection，
     * 响应链（ResponsePlugin -> ParserPlugin）遇到 204 空 body 不应抛异常，destination 原样为 ResponseInterface.
     */
    public function testResponse204WithOriginResponseDirection()
    {
        $response = new Response(204);
        $rocket = new Rocket();
        $rocket->setDirection(OriginResponseDirection::class)
            ->setDestinationOrigin($response)
            ->setDestination($response);

        $result = (new ResponsePlugin())->assembly($rocket, fn ($r) => (new ParserPlugin())->assembly($r, fn ($r2) => $r2));

        $destination = $result->getDestination();
        self::assertInstanceOf(ResponseInterface::class, $destination);
        self::assertEquals(204, $destination->getStatusCode());
        self::assertSame('', (string) $destination->getBody());
    }
}
