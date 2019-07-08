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

namespace getw\i18n;

use getw\Str;

/**
 * Class Translation
 * @package getw\i18n
 */
class Translation {

    public static function translate($message, $args = [], $options = []) {
        if (empty($args)) {
            $translate = Language::get($message);
        }
        //format
        if (isset($options['sprintf'])) {
            $translate = sprintf(Language::get($message), $args);
        } else {
            $translate = Str::format(Language::get($message), $args);
        }
     
        if (isset($options['escape'])) {
            $translate = Str::html_escape($translate);
        }
        if (isset($options['url'])) {
            $translate = urlencode($translate);
        }
        if (isset($options['js'])) {
            $translate = strtr($translate,
                    array('\\' => '\\\\', "'" => "\\'", '"' => '\\"', "\r" => '\\r', "\n" => '\\n', '</' => '<\/'));
        }
        return !empty($translate) ? $translate : $message;
    }

}
