<?php

declare(strict_types=1);

namespace Yansongda\Pay\Service;

use Yansongda\Artful\Contract\ServiceProviderInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\Douyin;

class DouyinServiceProvider implements ServiceProviderInterface
{
    /**
     * @throws ContainerException
     */
    public function register(mixed $data = null): void
    {
        $service = new Douyin();

        Pay::set(Douyin::class, $service);
        Pay::set('douyin', $service);
    }
}
