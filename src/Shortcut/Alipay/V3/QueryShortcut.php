<?php

declare(strict_types=1);

namespace Yansongda\Pay\Shortcut\Alipay\V3;

use Yansongda\Artful\Contract\ShortcutInterface;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Alipay\V3\AddRadarPlugin;
use Yansongda\Pay\Plugin\Alipay\V3\Pay\QueryPlugin;
use Yansongda\Pay\Plugin\Alipay\V3\ResponsePlugin;
use Yansongda\Pay\Plugin\Alipay\V3\StartPlugin;

class QueryShortcut implements ShortcutInterface
{
    public function getPlugins(array $params): array
    {
        return $this->defaultPlugins();
    }

    protected function defaultPlugins(): array
    {
        return [
            StartPlugin::class,
            QueryPlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }
}
