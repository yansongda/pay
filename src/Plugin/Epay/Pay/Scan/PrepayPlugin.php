<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Epay\Pay\Scan;

use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Epay\GeneralPlugin;

use function Yansongda\Pay\get_provider_config;

class PrepayPlugin extends GeneralPlugin
{
    protected function getService(): string
    {
        return 'atPay';
    }

    /**
     * @throws ServiceNotFoundException
     * @throws ContainerException
     */
    protected function doSomethingBefore(Rocket $rocket): void
    {
        if (!empty($rocket->getPayload()['notify_url'])) {
            return;
        }

        $params = $rocket->getParams();
        $config = get_provider_config('epay', $params);

        $backUrl = $config['notify_url'] ?? null;
        if (!$backUrl) {
            throw new InvalidConfigException(Exception::CONFIG_EPAY_INVALID, 'Missing Epay Config -- [notify_url]');
        }
        $rocket->mergePayload([
            'backUrl' => $backUrl,
        ]);
    }
}
