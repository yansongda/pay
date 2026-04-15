<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Traits;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
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
    public function testGetJsbUrl(): void
    {
        self::assertEquals('https://yansongda.cn', JsbTraitStub::getJsbUrl([], new Collection(['_url' => 'https://yansongda.cn'])));
        self::assertEquals('https://mybank.jsbchina.cn:577/eis/merchant/merchantServices.htm', JsbTraitStub::getJsbUrl(['mode' => Pay::MODE_NORMAL], new Collection()));
        self::assertEquals('https://epaytest.jsbchina.cn:9999/eis/merchant/merchantServices.htm', JsbTraitStub::getJsbUrl(['mode' => Pay::MODE_SANDBOX], new Collection()));
    }

    public function testVerifyJsbSignEmpty(): void
    {
        self::expectException(InvalidSignException::class);
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