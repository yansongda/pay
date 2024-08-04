<?php

namespace Yansongda\Pay\Tests\Provider;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Mockery;
use Psr\Http\Message\ResponseInterface;
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
        self::assertEquals('7376826336364513572', $result->get('data.order_id'));
        self::assertEquals('CgwIARDPKBjKMCABKAESTgpMTgGUG+Ms5klBoqYlsymcJWNMvgWCR8XH+9OO5vFPSl2zZcVKFX0sKRuG9zxMNlT43OJotxNNHaO4KLMbiqo6HYxMiRS5tkoeILFzexoA.W', $result->get('data.order_token'));
        self::assertEquals('771c1952ffb5e0744fc0ad1337aafa6a', $payload->get('sign'));
    }

    public function testClose()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(\Yansongda\Pay\Exception\Exception::PARAMS_METHOD_NOT_SUPPORTED);

        Pay::douyin()->close([]);
    }

    public function testCancel()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(\Yansongda\Pay\Exception\Exception::PARAMS_METHOD_NOT_SUPPORTED);

        Pay::douyin()->cancel([]);
    }

    public function testQuery()
    {
        $response = new Response(
            200,
            [],
            '{"err_no":0,"err_tips":"","out_order_no":"202408040747147327","order_id":"7398075047971440922","payment_info":{"total_fee":1,"order_status":"SUCCESS","pay_time":"2024-08-04 15:49:48","way":2,"channel_no":"","channel_gateway_no":"","seller_uid":"73744242495132490630","item_id":"","cp_extra":""}}',
        );

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn($response);
        Pay::set(HttpClientInterface::class, $http);

        $response = Pay::douyin()->query([
            'out_order_no' => '202406100423024876',
            '_return_rocket' => true,
        ]);

        $result = $response->getDestination();
        $payload = $response->getPayload();

        self::assertInstanceOf(Collection::class, $result);
        self::assertEquals('7517fb55db55327c396e5b7c9cb1be31', $payload->get('sign'));
        self::assertEquals('202408040747147327', $result->get('out_order_no'));
        self::assertEquals('7398075047971440922', $result->get('order_id'));
        self::assertEquals('SUCCESS', $result->get('payment_info.order_status'));
    }

    public function testQueryRefund()
    {
        $response = new Response(
            200,
            [],
            '{"err_no":0,"err_tips":"success","refundInfo":{"refund_no":"7398108028894988571","refund_amount":1,"refund_status":"SUCCESS","refunded_at":1722762159,"is_all_settled":true,"cp_extra":""}}',
        );

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn($response);
        Pay::set(HttpClientInterface::class, $http);

        $response = Pay::douyin()->query([
            'out_refund_no' => '202408040747147327',
            '_action' => 'refund',
            '_return_rocket' => true,
        ]);

        $result = $response->getDestination();
        $payload = $response->getPayload();

        self::assertInstanceOf(Collection::class, $result);
        self::assertEquals('fa6511979b1185cf98df2538f63ee1a3', $payload->get('sign'));
        self::assertEquals('7398108028894988571', $result->get('refundInfo.refund_no'));
    }

    public function testRefund()
    {
        $response = new Response(
            200,
            [],
            '{"err_no":0,"err_tips":"受理成功","refund_no":"7398108028894988571"}',
        );

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn($response);
        Pay::set(HttpClientInterface::class, $http);

        $response = Pay::douyin()->refund([
            'out_order_no' => '202408040747147327',
            'out_refund_no' => '202408040747147327',
            'reason' => '测试',
            'refund_amount' => 1,
            '_return_rocket' => true,
        ]);

        $result = $response->getDestination();
        $payload = $response->getPayload();

        self::assertInstanceOf(Collection::class, $result);
        self::assertEquals('32f9c840085091f5c84a346d87bd2b4e', $payload->get('sign'));
        self::assertEquals('7398108028894988571', $result->get('refund_no'));
    }

    public function testCallback()
    {
        $post = '{"msg":"{\"appid\":\"tt226e54d3bd581bf801\",\"cp_orderno\":\"202408041111312119\",\"cp_extra\":\"\",\"way\":\"2\",\"channel_no\":\"\",\"channel_gateway_no\":\"\",\"payment_order_no\":\"\",\"out_channel_order_no\":\"\",\"total_amount\":1,\"status\":\"SUCCESS\",\"seller_uid\":\"73744242495132490630\",\"extra\":\"\",\"item_id\":\"\",\"paid_at\":1722769986,\"message\":\"\",\"order_id\":\"7398108028895054107\"}","msg_signature":"840bdf067c1d6056becfe88735c8ebb7e1ab809c","nonce":"5280","timestamp":"1722769986","type":"payment"}';

        $callback = Pay::douyin()->callback(json_decode($post, true));
        self::assertInstanceOf(Collection::class, $callback);
        self::assertNotEmpty($callback->all());

        $request = new ServerRequest('POST', 'https://yansongda.cn/unipay/notify', [], $post);
        $callback = Pay::douyin()->callback($request);

        self::assertInstanceOf(Collection::class, $callback);
        self::assertNotEmpty($callback->all());
    }

    public function testSuccess()
    {
        $result = Pay::douyin()->success();

        self::assertInstanceOf(ResponseInterface::class, $result);
        self::assertStringContainsString('success', (string) $result->getBody());
    }
}
