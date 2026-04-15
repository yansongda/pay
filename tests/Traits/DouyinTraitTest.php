<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Traits;

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
    public function testGetDouyinUrlDefault(): void
    {
        self::assertSame(
            \Yansongda\Pay\Provider\Douyin::URL[Pay::MODE_NORMAL].'/mini',
            DouyinTraitStub::getDouyinUrl([], new Collection(['_url' => '/mini']))
        );
    }

    public function testGetDouyinUrlWithHttp(): void
    {
        self::assertSame(
            'https://example.com/douyin',
            DouyinTraitStub::getDouyinUrl([], new Collection(['_url' => 'https://example.com/douyin']))
        );
    }
}
