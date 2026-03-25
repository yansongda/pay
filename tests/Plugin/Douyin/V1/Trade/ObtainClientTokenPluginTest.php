<?php

namespace Yansongda\Pay\Tests\Plugin\Douyin\V1\Trade;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Yansongda\Artful\Contract\HttpClientInterface;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Douyin\V1\Trade\ObtainClientTokenPlugin;
use Yansongda\Pay\Tests\TestCase;

class ObtainClientTokenPluginTest extends TestCase
{
    protected ObtainClientTokenPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new ObtainClientTokenPlugin();
    }

    public function testWithProvidedAccessToken()
    {
        $params = ['_config' => 'trade', '_access_token' => 'pre_existing_token'];
        $rocket = (new Rocket())->setParams($params);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        self::assertEquals('pre_existing_token', $payload->get('_access_token'));
    }

    public function testFetchClientToken()
    {
        $response = new Response(
            200,
            [],
            '{"data":{"access_token":"fetched_token_123","expires_in":7200,"error_code":0}}',
        );

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn($response);
        Pay::set(HttpClientInterface::class, $http);

        $params = ['_config' => 'trade'];
        $rocket = (new Rocket())->setParams($params);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        self::assertEquals('fetched_token_123', $payload->get('_access_token'));
    }
}
