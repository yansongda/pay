<?php

namespace Yansongda\Pay\Contract;

interface ConfigInterface
{
    /**
     * Retrieve item from Collection.
     *
     * @param string $key     identifier of the entry to look for
     * @param mixed  $default default value of the entry when does not found
     *
     * @return mixed entry
     */
    public function get(string $key, $default = null);

    /**
     * To determine Whether the specified element exists.
     *
     * @param string $keys identifier of the entry to look for
     *
     * @return bool
     */
    public function has(string $keys);

    /**
     * Set the item value.
     *
     * @param string $key   identifier of the entry to set
     * @param mixed  $value the value that save to container
     */
    public function set(string $key, $value);
}
