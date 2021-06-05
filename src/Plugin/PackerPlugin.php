<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin;

use Closure;
use Yansongda\Pay\Contract\PackerInterface;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Rocket;

class PackerPlugin implements PluginInterface
{
    /**
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        /* @var Rocket $rocket */
        $rocket = $next($rocket);

        $packer = $this->getPacker($rocket);

        return $rocket->setDestination($packer->unpack($rocket->getDestination()));
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    protected function getPacker(Rocket $rocket): PackerInterface
    {
        $packer = Pay::get($rocket->getDirection() ?? PackerInterface::class);

        $packer = is_string($packer) ? Pay::get($packer) : $packer;

        if (!($packer instanceof PackerInterface)) {
            throw new InvalidConfigException(InvalidConfigException::INVALID_PACKER);
        }

        return $packer;
    }
}
