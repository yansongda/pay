<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Pay\Contract\DirectionInterface;
use Yansongda\Pay\Contract\PackerInterface;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Rocket;

class ParserPlugin implements PluginInterface
{
    /**
     * @throws ServiceNotFoundException
     * @throws ContainerException
     * @throws InvalidConfigException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        /* @var Rocket $rocket */
        $rocket = $next($rocket);

        /* @var ResponseInterface $response */
        $response = $rocket->getDestination();

        return $rocket->setDestination(
            $this->getDirection($rocket)->parse($this->getPacker($rocket), $response)
        );
    }

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws ServiceNotFoundException
     */
    protected function getDirection(Rocket $rocket): DirectionInterface
    {
        $packer = Pay::get($rocket->getDirection());

        $packer = is_string($packer) ? Pay::get($packer) : $packer;

        if (!$packer instanceof DirectionInterface) {
            throw new InvalidConfigException(Exception::INVALID_PARSER);
        }

        return $packer;
    }

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws ServiceNotFoundException
     */
    protected function getPacker(Rocket $rocket): PackerInterface
    {
        $packer = Pay::get($rocket->getPacker());

        $packer = is_string($packer) ? Pay::get($packer) : $packer;

        if (!$packer instanceof PackerInterface) {
            throw new InvalidConfigException(Exception::INVALID_PACKER);
        }

        return $packer;
    }
}
