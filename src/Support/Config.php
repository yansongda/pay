<?php

namespace Yansongda\Pay\Support;

use ArrayAccess;
use Yansongda\Pay\Exceptions\InvalidArgumentException;

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
    public function get($key = null, $default = null)
    {
        $config = $this->config;

        if (is_null($key)) {
            return $config;
        }

        if (isset($config[$key])) {
            return $config[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (!is_array($config) || !array_key_exists($segment, $config)) {
                return $default;
            }
            $config = $config[$segment];
        }

        return $config;
    }

    /**
     * set a config
     * @author JasonYan <me@yansongda.cn>
     * @version 2017-07-29
     * @param   string     $key   [description]
     * @param   [type]     $value [description]
     */
    public function set(string $key, $value)
    {
        if (is_null($key) || $key == '') {
            throw new InvalidArgumentException('Invalid config key.');
        }

        // 只支持三维数组，多余无意义
        $keys = explode('.', $key);
        switch (count($keys)) {
            case '1':
                $this->config[$key] = $value;
                break;
            case '2':
                $this->config[$keys[0]][$keys[1]] = $value;
                break;
            case '3':
                $this->config[$keys[0]][$keys[1]][$keys[3]] = $value;
                break;
            
            default:
                throw new InvalidArgumentException('Invalid config key.');
                break;
        }

        return $this->config;
    }

    /**
     * [offsetExists description]
     * @author JasonYan <me@yansongda.cn>
     * @version 2017-07-30
     * @param   [type]     $offset [description]
     * @return  [type]             [description]
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->config);
    }

    /**
     * [offsetGet description]
     * @author JasonYan <me@yansongda.cn>
     * @version 2017-07-30
     * @param   [type]     $offset [description]
     * @return  [type]             [description]
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * [offsetSet description]
     * @author JasonYan <me@yansongda.cn>
     * @version 2017-07-30
     * @param   [type]     $offset [description]
     * @param   [type]     $value  [description]
     * @return  [type]             [description]
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * [offsetUnset description]
     * @author JasonYan <me@yansongda.cn>
     * @version 2017-07-30
     * @param   [type]     $offset [description]
     * @return  [type]             [description]
     */
    public function offsetUnset($offset)
    {
        $this->set($offset, null);
    }

}