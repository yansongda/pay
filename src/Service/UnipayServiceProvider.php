<?php

declare(strict_types=1);

namespace Yansongda\Pay\Service;

use Yansongda\Pay\Contract\ParserInterface;
use Yansongda\Pay\Contract\ServiceProviderInterface;
use Yansongda\Pay\Parser\CollectionParser;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\Unipay;

class UnipayServiceProvider implements ServiceProviderInterface
{
    /**
     * @throws \Yansongda\Pay\Exception\ContainerException
     */
    public function register($data = null): void
    {
        $service = new Unipay();

        Pay::set(ParserInterface::class, CollectionParser::class);
        Pay::set(Unipay::class, $service);
        Pay::set('unipay', $service);
    }
}
