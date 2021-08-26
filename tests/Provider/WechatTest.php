<?php

namespace Yansongda\Pay\Tests\Provider;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Yansongda\Pay\Contract\HttpClientInterface;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Wechat\LaunchPlugin;
use Yansongda\Pay\Plugin\Wechat\PreparePlugin;
use Yansongda\Pay\Plugin\Wechat\SignPlugin;
use Yansongda\Pay\Tests\Stubs\Plugin\FooPluginStub;
use Yansongda\Pay\Tests\TestCase;

class WechatTest extends TestCase
{
    public function testShortcutNotFound()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::SHORTCUT_NOT_FOUND);

        Pay::wechat()->foo();
    }

    public function testShortcutIncompatible()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::SHORTCUT_NOT_FOUND);

        Pay::wechat()->foo();
    }

    public function testMergeCommonPlugins()
    {
        Pay::config([]);
        $plugins = [FooPluginStub::class];

        self::assertEquals(array_merge(
            [PreparePlugin::class],
            $plugins,
            [SignPlugin::class],
            [LaunchPlugin::class, ParserPlugin::class],
        ), Pay::wechat()->mergeCommonPlugins($plugins));
    }

    public function testCancel()
    {
        $response = new Response(
            200,
            [
                'Wechatpay-Nonce' => 'e59e78a6c3f7dfd7e84aabee71be0452',
                'Wechatpay-Signature' => 'Bb10ZUsON47E/qLjecjk6ESLt7obZCvCCAXAEoD1Q+K548fz9h6YBgR3PZzviTmjsA3/r22qEC3r/yelFAn4pl4rJBGqrjo4ODJkOPlaDnHZwYotDvf6RcASpKB9ExCb33hAijHCiMzr9V9skNrj5F9eXc96lNZN3R5MVLsTF97nV922JIzyCrZ668khYPrn1jl5pCBpYDQ3rskgmZ+nnjg7M9vRAfTowEydSEGtsKjXUSaaKui2RDUuX8ZwxVcBTRng978Gh9s4mdRxs+mlv3gP1xQHdpa0mYMG0yGzLcWOTgrkt27sAwFnuXj9WtlEAgz/1DYntujKPxilMVGRow==',
                'Wechatpay-Timestamp' => '1626444144',
                'Wechatpay-Serial' => '45F59D4DABF31918AFCEC556D5D2C6E376675D57',
            ],
            json_encode(['h5_url' => 'https://wx.tenpay.com/cgi-bin/mmpayweb-bin/checkmweb?prepay_id=wx16220223998099f898c5b24eed5c320000&package=4049184564'], JSON_UNESCAPED_SLASHES),
        );

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn($response);
        Pay::set(HttpClientInterface::class, $http);

        Pay::wechat()->close('foo');

        self::assertTrue(true);
    }
}
