<?php

declare(strict_types=1);

namespace Yansongda\Pay\Contract;

use Psr\Http\Message\ResponseInterface;

interface PackerInterface
{
    /**
     * @return mixed
     */
    public function unpack(ResponseInterface $response);
}
