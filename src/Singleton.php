<?php

// +----------------------------------------------------------------------
// | getw
// +----------------------------------------------------------------------
// | Copyright (c) 2014-2016 http://www.getw.com All rights reserved.
// | Copyright (c) 2014-2016 嘉兴领格信息技术有限公司，并保留所有权利。
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Allen <allen@getw.com>
// +----------------------------------------------------------------------

namespace getw;

/**
 * Trait Singleton
 * @package getw
 */
trait Singleton
{
    
    protected static $_instance;

    /**
     * Instance
     * @return mixed
     */
    final public static function instance()
    {
        if (is_null(static::$_instance)) {
            static::$_instance = new static;
        }
        return static::$_instance;
    }
}
