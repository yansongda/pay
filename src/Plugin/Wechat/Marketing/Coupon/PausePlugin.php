<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Marketing\Coupon;

use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;

use function Yansongda\Pay\get_wechat_config;

/**
 * @see https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter9_1_13.shtml
 */
class PausePlugin extends GeneralPlugin
{
    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    protected function doSomething(Rocket $rocket): void
    {
        $config = get_wechat_config($rocket->getParams());
        $payload = $rocket->getPayload();

        if (!$payload->has('stock_creator_mchid')) {
            $rocket->mergePayload(['stock_creator_mchid' => $config['mch_id']]);
        }

        $rocket->getPayload()->forget('stock_id');
    }

    /**
     * @throws InvalidParamsException
     */
    protected function getUri(Rocket $rocket): string
    {
        $payload = $rocket->getPayload();

        if (!$payload->has('stock_id')) {
            throw new InvalidParamsException(Exception::MISSING_NECESSARY_PARAMS);
        }

        return 'v3/marketing/favor/stocks/'.$payload->get('stock_id').'/pause';
    }
}
