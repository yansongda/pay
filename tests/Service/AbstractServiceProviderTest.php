<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Contract\ProviderInterface;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Service\AbstractServiceProvider;
use Yansongda\Supports\Collection;

it('注册', function () {
    $provider = new ConcreteServiceProvider();
    $provider->register();

    expect(Pay::get(ConcreteProvider::class))->toBeInstanceOf(ConcreteProvider::class);
    expect(Pay::get('concrete_provider'))->toBe(Pay::get(ConcreteProvider::class));
});

class ConcreteServiceProvider extends AbstractServiceProvider
{
protected function makeService(): ProviderInterface
{
    return new ConcreteProvider();
}

protected function getProviderName(): string
{
    return 'concrete_provider';
}
}

class ConcreteProvider implements ProviderInterface
{
public function pay(array $plugins, array $params): Collection|MessageInterface|Rocket|null
{
    return null;
}

public function query(array $order): Collection|Rocket
{
    return new Collection();
}

public function cancel(array $order): Collection|Rocket
{
    return new Collection();
}

public function close(array $order): Collection|Rocket
{
    return new Collection();
}

public function refund(array $order): Collection|Rocket
{
    return new Collection();
}

public function callback(array|ServerRequestInterface|null $contents = null, ?array $params = null): Collection|Rocket
{
    return new Collection();
}

public function success(): ResponseInterface
{
    return new Response();
}
}
