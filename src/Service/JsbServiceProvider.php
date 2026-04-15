<?php

declare(strict_types=1);

namespace Yansongda\Pay\Service;

use Yansongda\Pay\Provider\Jsb;

class JsbServiceProvider extends AbstractServiceProvider
{
    protected function getProviderClass(): string
    {
        return Jsb::class;
    }

    protected function getProviderName(): string
    {
        return 'jsb';
    }
}
