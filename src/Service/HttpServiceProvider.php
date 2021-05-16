<?php

declare(strict_types=1);

namespace Yansongda\Pay\Service;

use GuzzleHttp\Client;
use Yansongda\Pay\Contract\ConfigInterface;
use Yansongda\Pay\Contract\HttpClientInterface;
use Yansongda\Pay\Contract\ServiceProviderInterface;
use Yansongda\Pay\Pay;

class HttpServiceProvider implements ServiceProviderInterface
{
    public function register(Pay $pay, ?array $data = null): void
    {
        $service = function () use ($pay) {
            /* @var \Yansongda\Supports\Config $config */
            $config = Pay::get(ConfigInterface::class);

            return new Client($config->get('http', []));
        };

        Pay::set(HttpClientInterface::class, $service);
    }
}
