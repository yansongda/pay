<?php

declare(strict_types=1);

namespace Yansongda\Pay\Contract;

interface ContainerInterface extends \Psr\Container\ContainerInterface
{
    public function make(string $name, array $parameters = []): mixed;

    public function set(string $name, mixed $entry): mixed;
}
