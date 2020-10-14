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
    /**
     * {@inheritdoc}
     */
    public function prepare(array $data): void
    {
        Pay::set(HttpClientInterface::class, null);
    }

    /**
     * {@inheritdoc}
     */
    public function register(Pay $pay): void
    {
        $service = function () use ($pay) {
            /* @var \Yansongda\Supports\Config $config */
            $config = Pay::get(ConfigInterface::class);

            return $pay::make(Client::class, [
                'config' => $config->get('http', []),
            ]);
        };

        $pay::set(HttpClientInterface::class, $service);
    }
}
