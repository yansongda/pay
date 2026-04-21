<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Stubs;

use Yansongda\Artful\Contract\ServiceProviderInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Pay;

class FooServiceProviderStub implements ServiceProviderInterface
{
    /**
     * @throws ContainerException
     */
    public function register(mixed $data = null): void
    {
        Pay::set('foo', 'bar');
    }
}
