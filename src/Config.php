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


/**
 * Class Config
 * @package getw
 */
class Config
{

    /**
     *
     * @var array Config Item
     */
    private static $data = array();

    /**
     *
     * @param string $name Config配置文件名
     * @return array
     */
    public static function load($name)
    {
        if (!array_key_exists($name, static::$data)) {
            if (!defined('CONFIG_PATH')) {
                static::$data[$name] = [];
                return;
            }
            $configFileName = CONFIG_PATH . DIRECTORY_SEPARATOR . $name . '.php';
            if (is_file($configFileName)) {
                static::$data[$name] = include $configFileName;
            }
        }
    }

    /**
     *
     * @return array 返回所有Config数据
     */
    public static function all()
    {
        return static::$data;
    }

    /**
     *
     * @param string $name
     * @param array|string $default
     * @return array|string
     */
    public static function get($name, $default = NULL)
    {
        $names = explode('.', $name);
        if (isset($names[0]) && !Arr::has(static::$data, $names[0])) {
            static::load($names[0]);
        }
        return Arr::get(static::$data, $name, $default);
    }

    /**
     *
     * @param string $name
     * @param array|string $value
     * @return \getw\Config
     */
    public static function set($name, $value = array())
    {
        $names = explode('.', $name);
        if (isset($names[0]) && !Arr::has(static::$data, $names[0])) {
            static::load($names[0]);
        }
        Arr::set(static::$data, $name, $value);
    }

    /**
     *
     * @param string $name
     * @return array|string
     */
    public static function has($name)
    {
        return Arr::has(static::$data, $name);
    }

    /**
     * Remove Config Item
     * @param string $name
     * @return \getw\Config
     */
    public static function remove($name)
    {
        Arr::set(static::$data, $name, array());
    }

}
