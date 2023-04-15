<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Fund\Profitsharing;

use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;

use function Yansongda\Pay\get_wechat_config;

/**
 * @see https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter8_1_4.shtml
 */
class QueryReturnPlugin extends GeneralPlugin
{
    protected function getMethod(): string
    {
        return 'GET';
    }

    protected function doSomething(Rocket $rocket): void
    {
        $rocket->setPayload(null);
    }

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    protected function getUri(Rocket $rocket): string
    {
        $payload = $rocket->getPayload();
        $config = get_wechat_config($rocket->getParams());

        if (!$payload->has('out_return_no') || !$payload->has('out_order_no')) {
            throw new InvalidParamsException(Exception::MISSING_NECESSARY_PARAMS);
        }

        $url = 'v3/profitsharing/return-orders/'.
            $payload->get('out_return_no').
            '?out_order_no='.$payload->get('out_order_no');

        if (Pay::MODE_SERVICE === ($config['mode'] ?? null)) {
            $url .= '&sub_mchid='.$payload->get('sub_mchid', $config['sub_mch_id'] ?? '');
        }

        return $url;
    }
}
