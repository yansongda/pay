<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Stubs;

use Yansongda\Pay\Contract\ServiceProviderInterface;
use Yansongda\Pay\Pay;

class FooServiceProviderStub implements ServiceProviderInterface
{
    /**
     * @throws \Yansongda\Pay\Exception\ContainerException
     */
    public function register(Pay $pay, ?array $data = null): void
    {
        $pay::set('foo', 'bar');
    }
}
