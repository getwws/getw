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

namespace getw\db;

/**
 * Class Expr
 * @package getw\db
 */
class Expr {

    public $raw;

    public function __construct($value) {
        $this->raw = $value;
    }

    /**
     * Make Expr
     * @param mixed $value
     * @return Expr
     */
    public static function make($value) {
        return new static($value);
    }

    public function __toString() {
        return $this->raw;
    }

}
