<?php

declare(strict_types=1);

namespace Yansongda\Pay\Traits;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Pay\Config\DouyinConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Provider\Douyin;
use Yansongda\Supports\Collection;

trait DouyinTrait
{
    use ProviderConfigTrait;

    /**
     * @throws InvalidParamsException
     */
    public static function getDouyinUrl(DouyinConfig $config, ?Collection $payload): string
    {
        $url = self::getRadarUrl($config, $payload);

        if (empty($url)) {
            throw new InvalidParamsException(Exception::PARAMS_DOUYIN_URL_MISSING, '参数异常: 抖音 `_url` 参数缺失：你可能用错插件顺序，应该先使用 `业务插件`');
        }

        if (str_starts_with($url, 'http')) {
            return $url;
        }

        return Douyin::URL[$config->getMode()].$url;
    }
}
