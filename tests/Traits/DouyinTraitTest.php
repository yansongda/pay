<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Traits;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Pay\Traits\DouyinTrait;
use Yansongda\Supports\Collection;

class DouyinTraitStub
{
    use DouyinTrait;
}

class DouyinTraitTest extends TestCase
{
    public function testGetDouyinUrl(): void
    {
        self::assertEquals('https://yansongda.cn', DouyinTraitStub::getDouyinUrl([], new Collection(['_url' => 'https://yansongda.cn'])));
        self::assertEquals('https://developer.toutiao.com/api/v1/yansongda', DouyinTraitStub::getDouyinUrl([], new Collection(['_url' => 'api/v1/yansongda'])));
        self::assertEquals('https://developer.toutiao.com/api/v1/service/yansongda', DouyinTraitStub::getDouyinUrl(['mode' => Pay::MODE_SERVICE], new Collection(['_service_url' => 'api/v1/service/yansongda'])));
        self::assertEquals('https://developer.toutiao.com/api/v1/service/yansongda', DouyinTraitStub::getDouyinUrl(['mode' => Pay::MODE_SERVICE], new Collection(['_url' => 'foo', '_service_url' => 'api/v1/service/yansongda'])));

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_DOUYIN_URL_MISSING);
        DouyinTraitStub::getDouyinUrl([], new Collection([]));
    }
}