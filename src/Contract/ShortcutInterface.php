<?php

declare(strict_types=1);

namespace Yansongda\Pay\Contract;

interface ShortcutInterface
{
    /**
     * @return PluginInterface[]|string[]
     */
    public function getPlugins(array $params): array;
}
