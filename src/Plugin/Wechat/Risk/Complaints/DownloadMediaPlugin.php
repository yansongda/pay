<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Risk\Complaints;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Parser\OriginResponseParser;
use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;

/**
 * @see https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter10_2_18.shtml
 */
class DownloadMediaPlugin extends GeneralPlugin
{
    /**
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     */
    protected function getUri(Rocket $rocket): string
    {
        $payload = $rocket->getPayload();

        if (is_null($payload->get('media_url'))) {
            throw new InvalidParamsException(Exception::MISSING_NECESSARY_PARAMS);
        }

        return $payload->get('media_url');
    }

    protected function getMethod(): string
    {
        return 'GET';
    }

    protected function doSomething(Rocket $rocket): void
    {
        $rocket->setDirection(OriginResponseParser::class);

        $rocket->setPayload(null);
    }
}
