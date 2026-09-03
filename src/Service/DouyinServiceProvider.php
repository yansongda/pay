<?php

declare(strict_types=1);

namespace Yansongda\Pay\Service;

use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\Douyin;

class DouyinServiceProvider extends AbstractServiceProvider
{
    protected function getProviderClass(): string
    {
        return Douyin::class;
    }

    protected function getProviderName(): string
    {
        return Pay::PROVIDER_DOUYIN;
    }
}
