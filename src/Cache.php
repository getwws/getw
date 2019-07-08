<?php

// +----------------------------------------------------------------------
// | GETW © OpenSource CMS
// +----------------------------------------------------------------------
// | Copyright (c) 2014-2016 http://www.getw.com All rights reserved.
// | Copyright (c) 2014-2016 嘉兴领格信息技术有限公司，并保留所有权利。
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Allen <allen@lg4.cn>
// +----------------------------------------------------------------------

namespace getw;

use Stash\Pool;

/**
 * Class Cache
 * @package getw
 */
class Cache {

    protected static $instances = [];

    /**
     * 
     * @param string $name
     * @return \Stash\Pool
     */
    public static function store($name = NULL) {
        if ($name == NULL) {
            $name = Config::get('cache.default', 'file');
        }
        return static::factory($name, Config::get('cache.' . $name));
    }

    /**
     * Create Cache
     * @param string $name
     * @param array $options
     * @return mixed|Pool
     */
    public static function factory($name = 'file', $options = []) {
        if (!isset(static::$instances[$name])) {
            if ($name == 'file') {
                $driver = new Pool(new \Stash\Driver\FileSystem($options));
            } else if ($name == 'apc') {
                $driver = new Pool(new \Stash\Driver\Apc($options));
            } else if ($name == 'memcache') {
                $driver = new Pool(new \Stash\Driver\Memcache($options));
            } else if ($name == 'redis') {
                $driver = new Pool(new \Stash\Driver\Redis($options));
            }
        } else {
            return static::$instances[$name];
        }
        static::$instances[$name] = $driver;
        return $driver;
    }

}
