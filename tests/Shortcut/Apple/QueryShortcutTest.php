<?php

declare(strict_types=1);

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

it('默认', function () {
    $shortcut = new QueryShortcut();
    expect($shortcut->getPlugins([]))->toEqual([
        StartPlugin::class,
        QueryPlugin::class,
        AddRadarPlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('交易动作', function () {
    $shortcut = new QueryShortcut();
    expect($shortcut->getPlugins(['_action' => 'transaction']))->toEqual([
        StartPlugin::class,
        QueryPlugin::class,
        AddRadarPlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('历史动作', function () {
    $shortcut = new QueryShortcut();
    expect($shortcut->getPlugins(['_action' => 'history']))->toEqual([
        StartPlugin::class,
        QueryHistoryPlugin::class,
        AddRadarPlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('订阅动作', function () {
    $shortcut = new QueryShortcut();
    expect($shortcut->getPlugins(['_action' => 'subscriptions']))->toEqual([
        StartPlugin::class,
        QuerySubscriptionsPlugin::class,
        AddRadarPlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('未知动作', function () {
    $shortcut = new QueryShortcut();

    $shortcut->getPlugins(['_action' => 'foo']);
})->throws(InvalidParamsException::class, '不支持的 _action [foo]', Exception::PARAMS_SHORTCUT_ACTION_INVALID);
it('HTTP mock 下的查询端到端', function () {
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

    expect((string) $captured->getUri())->toBe('https://api.storekit-sandbox.apple.com/inApps/v2/history/1000000047447934749');
    expect($captured->getHeaderLine('Authorization'))->toStartWith('Bearer ');
    expect(explode('.', substr($captured->getHeaderLine('Authorization'), 7)))->toHaveCount(3);
    expect($result)->toBeInstanceOf(\Yansongda\Supports\Collection::class);
    expect((string) $result->get('revision'))->toBe('1');

    \Mockery::close();
});

