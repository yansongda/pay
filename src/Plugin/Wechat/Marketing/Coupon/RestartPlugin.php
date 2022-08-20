<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Marketing\Coupon;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;

/**
 * @see https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter9_1_14.shtml
 */
class RestartPlugin extends GeneralPlugin
{
    /**
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     */
    protected function doSomething(Rocket $rocket): void
    {
        $payload = $rocket->getPayload();

        if (is_null($payload->get('stock_creator_mchid'))) {
            throw new InvalidParamsException(Exception::MISSING_NECESSARY_PARAMS);
        }

        $rocket->setPayload(new Collection([
            'stock_creator_mchid' => $payload->get('stock_creator_mchid'),
        ]));
    }

    /**
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     */
    protected function getUri(Rocket $rocket): string
    {
        $payload = $rocket->getPayload();

        if (is_null($payload->get('stock_id'))) {
            throw new InvalidParamsException(Exception::MISSING_NECESSARY_PARAMS);
        }

        return 'v3/marketing/favor/stocks/'.$payload->get('stock_id').'/restart';
    }
}
