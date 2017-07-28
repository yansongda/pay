<?php

namespace Yansongda\Pay\Support;

use ArrayAccess;

/**
 * Class Config.
 */
class Config implements ArrayAccess
{
    /**
     * @var array
     */
    protected $config;

    /**
     * Config constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * get a config
     * @author JasonYan <me@yansongda.cn>
     * @version 2017-07-28
     * @param   [type]     $key     [description]
     * @param   [type]     $default [description]
     * @return  [type]              [description]
     */
    public function get($key, $default = null)
    {
        if (is_null($key)) {
            return $this->config;
        }

        if (isset($this->config[$key])) {
            return $this->config[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (! array_key_exists($segment, $this->config)) {
                return $default;
            }
            return $this->config[$segment];
        }
    }

}