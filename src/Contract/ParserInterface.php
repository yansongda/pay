<?php

declare(strict_types=1);

namespace Yansongda\Pay\Contract;

use Psr\Http\Message\ResponseInterface;

interface ParserInterface
{
    /**
     * @return mixed
     */
    public function parse(?ResponseInterface $response);
}
