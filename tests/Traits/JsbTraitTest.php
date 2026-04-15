<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Traits;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Pay\Traits\JsbTrait;
use Yansongda\Supports\Collection;

class JsbTraitStub
{
    use JsbTrait;
}

class JsbTraitTest extends TestCase
{
    public function testGetJsbUrlWithHttp(): void
    {
        self::assertSame(
            'https://example.com/jsb',
            JsbTraitStub::getJsbUrl([], new Collection(['_url' => 'https://example.com/jsb']))
        );
    }

    public function testGetJsbUrlDefault(): void
    {
        self::assertSame(
            \Yansongda\Pay\Provider\Jsb::URL[Pay::MODE_NORMAL],
            JsbTraitStub::getJsbUrl([], new Collection())
        );
    }

    public function testVerifyJsbSignEmpty(): void
    {
        self::expectException(\Yansongda\Pay\Exception\InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_EMPTY);

        JsbTraitStub::verifyJsbSign(['jsb_public_cert_path' => 'x'], 'content', '');
    }

    public function testVerifyJsbSignMissingConfig(): void
    {
        self::expectException(InvalidConfigException::class);
        self::expectExceptionCode(Exception::CONFIG_JSB_INVALID);

        JsbTraitStub::verifyJsbSign([], 'content', 'sign');
    }
}
