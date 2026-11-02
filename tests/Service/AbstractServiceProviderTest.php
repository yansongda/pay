<?php

declare(strict_types=1);

use Yansongda\Pay\Pay;
use Yansongda\Pay\Tests\Stubs\ConcreteServiceProvider;
use Yansongda\Pay\Tests\Stubs\ConcreteProvider;

it('注册', function () {
    $provider = new ConcreteServiceProvider();
    $provider->register();

    expect(Pay::get(ConcreteProvider::class))->toBeInstanceOf(ConcreteProvider::class);
    expect(Pay::get('concrete_provider'))->toBe(Pay::get(ConcreteProvider::class));
});

