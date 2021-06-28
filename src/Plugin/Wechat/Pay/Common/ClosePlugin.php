<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\Common;

use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Parser\OriginResponseParser;
use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;

class ClosePlugin extends GeneralPlugin
{
    /**
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     */
    protected function getUri(Rocket $rocket): string
    {
        $payload = $rocket->getPayload();

        if (is_null($payload->get('out_trade_no'))) {
            throw new InvalidParamsException(InvalidParamsException::MISSING_NECESSARY_PARAMS);
        }

        return 'v3/pay/transactions/out-trade-no/'.
            $payload->get('out_trade_no').
            '/close';
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    protected function doSomething(Rocket $rocket): void
    {
        $rocket->setDirection(OriginResponseParser::class);

        $config = get_wechat_config($rocket->getParams());

        $rocket->setPayload(new Collection([
            'mchid' => $config->get('mch_id', ''),
        ]));
    }
}
