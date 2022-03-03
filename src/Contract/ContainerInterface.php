<?php

declare(strict_types=1);

namespace Yansongda\Pay\Contract;

interface ContainerInterface extends \Psr\Container\ContainerInterface
{
    /**
     * factory make.
     *
     * @return mixed
     */
    public function make(string $name, array $parameters = []);

    /**
     * @param mixed $entry
     *
     * @return mixed
     */
    public function set(string $name, $entry);
}
