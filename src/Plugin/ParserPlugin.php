<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin;

use Closure;
use Yansongda\Pay\Contract\PackerInterface;
use Yansongda\Pay\Contract\ParserInterface;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Rocket;

class ParserPlugin implements PluginInterface
{
    /**
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        /* @var Rocket $rocket */
        $rocket = $next($rocket);

        /* @var \Psr\Http\Message\ResponseInterface $response */
        $response = $rocket->getDestination();

        return $rocket->setDestination(
            $this->getParser($rocket)->parse($this->getPacker($rocket), $response)
        );
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    protected function getParser(Rocket $rocket): ParserInterface
    {
        $packer = Pay::get($rocket->getDirection());

        $packer = is_string($packer) ? Pay::get($packer) : $packer;

        if (!($packer instanceof ParserInterface)) {
            throw new InvalidConfigException(Exception::INVALID_PARSER);
        }

        return $packer;
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    protected function getPacker(Rocket $rocket): PackerInterface
    {
        $packer = Pay::get($rocket->getPacker());

        $packer = is_string($packer) ? Pay::get($packer) : $packer;

        if (!($packer instanceof PackerInterface)) {
            throw new InvalidConfigException(Exception::INVALID_PACKER);
        }

        return $packer;
    }
}
