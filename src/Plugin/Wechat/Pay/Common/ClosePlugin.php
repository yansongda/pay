<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\Common;

use Yansongda\Pay\Direction\OriginResponseDirection;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;

use function Yansongda\Pay\get_wechat_config;

class ClosePlugin extends GeneralPlugin
{
    /**
     * @throws InvalidParamsException
     */
    protected function getUri(Rocket $rocket): string
    {
        $payload = $rocket->getPayload();

        if (!$payload->has('out_trade_no')) {
            throw new InvalidParamsException(Exception::MISSING_NECESSARY_PARAMS);
        }

        return 'v3/pay/transactions/out-trade-no/'.
            $payload->get('out_trade_no').
            '/close';
    }

    /**
     * @throws InvalidParamsException
     */
    protected function getPartnerUri(Rocket $rocket): string
    {
        $payload = $rocket->getPayload();

        if (!$payload->has('out_trade_no')) {
            throw new InvalidParamsException(Exception::MISSING_NECESSARY_PARAMS);
        }

        return 'v3/pay/partner/transactions/out-trade-no/'.
            $payload->get('out_trade_no').
            '/close';
    }

    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    protected function doSomething(Rocket $rocket): void
    {
        $rocket->setDirection(OriginResponseDirection::class);

        $config = get_wechat_config($rocket->getParams());

        $body = [
            'mchid' => $config['mch_id'] ?? '',
        ];

        if (Pay::MODE_SERVICE == ($config['mode'] ?? null)) {
            $body = [
                'sp_mchid' => $config['mch_id'] ?? '',
                'sub_mchid' => $rocket->getPayload()->get('sub_mchid', $config['sub_mch_id'] ?? ''),
            ];
        }

        $rocket->setPayload(new Collection($body));
    }
}
