<?php

declare(strict_types=1);

namespace Yansongda\Pay\Traits;

use Yansongda\Pay\Pay;
use Yansongda\Pay\Rocket;

trait SupportServiceProviderTrait
{
    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    protected function loadAlipayServiceProvider(Rocket $rocket): void
    {
        $params = $rocket->getParams();
        $config = get_alipay_config($params);
        $serviceProviderId = $config->get('service_provider_id');

        if (Pay::MODE_SERVICE !== $config->get('mode', Pay::MODE_NORMAL) ||
            empty($serviceProviderId)) {
            return;
        }

        $rocket->mergeParams([
            'extend_params' => array_merge($params['extend_params'] ?? [], ['sys_service_provider_id' => $serviceProviderId]),
        ]);
    }
}
