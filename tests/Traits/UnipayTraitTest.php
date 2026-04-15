<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Traits;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Pay\Traits\UnipayTrait;
use Yansongda\Supports\Collection;

class UnipayTraitStub
{
    use UnipayTrait;
}

class UnipayTraitTest extends TestCase
{
    public function testGetUnipayUrlDefault(): void
    {
        self::assertSame(
            \Yansongda\Pay\Provider\Unipay::URL[Pay::MODE_NORMAL].'/test',
            UnipayTraitStub::getUnipayUrl([], new Collection(['_url' => '/test']))
        );
    }

    public function testGetUnipayUrlWithHttp(): void
    {
        self::assertSame(
            'https://example.com/unipay',
            UnipayTraitStub::getUnipayUrl([], new Collection(['_url' => 'https://example.com/unipay']))
        );
    }

    public function testVerifyUnipaySignEmpty(): void
    {
        self::expectException(\Yansongda\Pay\Exception\InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_EMPTY);

        UnipayTraitStub::verifyUnipaySign([], 'abc', '');
    }

    public function testVerifyUnipaySignMissingConfig(): void
    {
        self::expectException(InvalidConfigException::class);
        self::expectExceptionCode(Exception::CONFIG_UNIPAY_INVALID);

        UnipayTraitStub::verifyUnipaySign([], 'abc', 'sign');
    }

    public function testGetUnipaySignQra(): void
    {
        self::assertSame(
            strtoupper(md5('a=1&key=secret')),
            UnipayTraitStub::getUnipaySignQra(['mch_secret_key' => 'secret'], ['a' => '1'])
        );
    }
}
