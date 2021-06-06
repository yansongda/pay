<?php

declare(strict_types=1);

namespace Yansongda\Pay\Contract;

interface ShortcutInterface
{
    /**
     * @author yansongda <me@yansongda.cn>
     *
     * @return \Yansongda\Pay\Contract\PluginInterface[]|string[]
     */
    public function getPlugins(array $params): array;
}
