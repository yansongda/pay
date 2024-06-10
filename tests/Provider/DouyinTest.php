<?php

namespace Yansongda\Pay\Tests\Provider;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Yansongda\Artful\Contract\HttpClientInterface;
use Yansongda\Artful\Exception\Exception;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\AddRadarPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Douyin\V1\Pay\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Pay\ResponsePlugin;
use Yansongda\Pay\Tests\Stubs\Plugin\FooPluginStub;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class DouyinTest extends TestCase
{
    public function testShortcutNotFound()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_SHORTCUT_INVALID);

        Pay::douyin()->foo();
    }

    public function testShortcutIncompatible()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_SHORTCUT_INVALID);

        Pay::douyin()->foo();
    }

    public function testMergeCommonPlugins()
    {
        Pay::config([]);
        $plugins = [FooPluginStub::class];

        self::assertEquals(array_merge(
            [StartPlugin::class],
            $plugins,
            [AddPayloadSignaturePlugin::class, AddPayloadBodyPlugin::class, AddRadarPlugin::class, ResponsePlugin::class, ParserPlugin::class],
        ), Pay::douyin()->mergeCommonPlugins($plugins));
    }

    public function testCallMini()
    {
        $response = new Response(
            200,
            [],
            '{"err_no":0,"err_tips":"","data":{"order_id":"7376826336364513572","order_token":"CgwIARDPKBjKMCABKAESTgpMTgGUG+Ms5klBoqYlsymcJWNMvgWCR8XH+9OO5vFPSl2zZcVKFX0sKRuG9zxMNlT43OJotxNNHaO4KLMbiqo6HYxMiRS5tkoeILFzexoA.W"}}',
        );

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn($response);
        Pay::set(HttpClientInterface::class, $http);

        $response = Pay::douyin()->mini([
            'out_order_no' => '202406100423024876',
            'total_amount' => 1,
            'subject' => '闫嵩达 - test - subject - 01',
            'body' => '闫嵩达 - test - body - 01',
            'valid_time' => 600,
            // 'notify_url' => 'https://yansongda.cn/unipay/notify',
            '_return_rocket' => true,
        ]);

        $result = $response->getDestination();
        $payload = $response->getPayload();

        self::assertInstanceOf(Collection::class, $result);
        self::assertEquals('7376826336364513572', $result->get('order_id'));
        self::assertEquals('CgwIARDPKBjKMCABKAESTgpMTgGUG+Ms5klBoqYlsymcJWNMvgWCR8XH+9OO5vFPSl2zZcVKFX0sKRuG9zxMNlT43OJotxNNHaO4KLMbiqo6HYxMiRS5tkoeILFzexoA.W', $result->get('order_token'));
        self::assertEquals('771c1952ffb5e0744fc0ad1337aafa6a', $payload->get('sign'));
    }
}
