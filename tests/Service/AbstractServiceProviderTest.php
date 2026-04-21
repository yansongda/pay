<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Service;

use Yansongda\Pay\Pay;
use Yansongda\Pay\Service\AbstractServiceProvider;
use Yansongda\Pay\Tests\TestCase;

class AbstractServiceProviderTest extends TestCase
{
    public function testRegister(): void
    {
        $provider = new ConcreteServiceProvider();
        $provider->register();

        self::assertInstanceOf(ConcreteProvider::class, Pay::get(ConcreteProvider::class));
        self::assertSame(Pay::get(ConcreteProvider::class), Pay::get('concrete_provider'));
    }
}

class ConcreteServiceProvider extends AbstractServiceProvider
{
    protected function getProviderClass(): string
    {
        return ConcreteProvider::class;
    }

    protected function getProviderName(): string
    {
        return 'concrete_provider';
    }
}

class ConcreteProvider {}
