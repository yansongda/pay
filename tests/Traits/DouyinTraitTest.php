<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Traits;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Pay\Config\DouyinConfig;
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
        $normalConfig = new DouyinConfig([
            'mch_id' => '73744242495132490630',
            'mch_secret_token' => 'douyin_mini_token',
            'mch_secret_salt' => 'oDxWDBr4U7FAAQ8hnGDm29i4A6pbTMDKme4WLLvA',
            'mini_app_id' => 'tt226e54d3bd581bf801',
        ]);
        $serviceConfig = new DouyinConfig([
            'mch_id' => '73744242495132490630',
            'mch_secret_token' => 'douyin_mini_token',
            'mch_secret_salt' => 'oDxWDBr4U7FAAQ8hnGDm29i4A6pbTMDKme4WLLvA',
            'mini_app_id' => 'tt226e54d3bd581bf801',
            'mode' => Pay::MODE_SERVICE,
        ]);

        self::assertEquals('https://yansongda.cn', DouyinTraitStub::getDouyinUrl($normalConfig, new Collection(['_url' => 'https://yansongda.cn'])));
        self::assertEquals('https://developer.toutiao.com/api/v1/yansongda', DouyinTraitStub::getDouyinUrl($normalConfig, new Collection(['_url' => 'api/v1/yansongda'])));
        self::assertEquals('https://developer.toutiao.com/api/v1/service/yansongda', DouyinTraitStub::getDouyinUrl($serviceConfig, new Collection(['_service_url' => 'api/v1/service/yansongda'])));
        self::assertEquals('https://developer.toutiao.com/api/v1/service/yansongda', DouyinTraitStub::getDouyinUrl($serviceConfig, new Collection(['_url' => 'foo', '_service_url' => 'api/v1/service/yansongda'])));

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_DOUYIN_URL_MISSING);
        DouyinTraitStub::getDouyinUrl($normalConfig, new Collection([]));
    }
}
