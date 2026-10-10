<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Apple;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Apple\AddRadarPlugin;
use Yansongda\Pay\Plugin\Apple\Pay\QueryHistoryPlugin;
use Yansongda\Pay\Plugin\Apple\Pay\QueryPlugin;
use Yansongda\Pay\Plugin\Apple\Pay\QuerySubscriptionsPlugin;
use Yansongda\Pay\Plugin\Apple\ResponsePlugin;
use Yansongda\Pay\Shortcut\Apple\QueryShortcut;
use Yansongda\Pay\Tests\TestCase;

class QueryShortcutTest extends TestCase
{
    protected QueryShortcut $shortcut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shortcut = new QueryShortcut();
    }

    public function testDefault()
    {
        self::assertEquals([
            StartPlugin::class,
            QueryPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $this->shortcut->getPlugins([]));
    }

    public function testTransactionAction()
    {
        self::assertEquals([
            StartPlugin::class,
            QueryPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $this->shortcut->getPlugins(['_action' => 'transaction']));
    }

    public function testHistoryAction()
    {
        self::assertEquals([
            StartPlugin::class,
            QueryHistoryPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $this->shortcut->getPlugins(['_action' => 'history']));
    }

    public function testSubscriptionsAction()
    {
        self::assertEquals([
            StartPlugin::class,
            QuerySubscriptionsPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $this->shortcut->getPlugins(['_action' => 'subscriptions']));
    }

    public function testUnknownAction()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_SHORTCUT_ACTION_INVALID);
        self::expectExceptionMessage('不支持的 _action [foo]');

        $this->shortcut->getPlugins(['_action' => 'foo']);
    }
    public function testQueryEndToEndWithHttpMock(): void
    {
        $http = \Mockery::mock(\GuzzleHttp\Client::class);
        $captured = null;
        $http->shouldReceive('sendRequest')->once()->with(\Mockery::on(function ($request) use (&$captured) {
            $captured = $request;

            return true;
        }))->andReturn(new \GuzzleHttp\Psr7\Response(200, [], (string) json_encode(['signedTransactions' => [], 'revision' => '1'])));

        \Yansongda\Pay\Pay::set(\Yansongda\Artful\Contract\HttpClientInterface::class, $http);

        $result = \Yansongda\Pay\Pay::apple()->query([
            'transaction_id' => '1000000047447934749',
            '_action' => 'history',
        ]);

        self::assertSame('https://api.storekit-sandbox.apple.com/inApps/v2/history/1000000047447934749', (string) $captured->getUri());
        self::assertStringStartsWith('Bearer ', $captured->getHeaderLine('Authorization'));
        self::assertCount(3, explode('.', substr($captured->getHeaderLine('Authorization'), 7)));
        self::assertInstanceOf(\Yansongda\Supports\Collection::class, $result);
        self::assertSame('1', (string) $result->get('revision'));

        \Mockery::close();
    }

}
