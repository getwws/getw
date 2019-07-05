<?php

// +----------------------------------------------------------------------
// | H1CMS © OpenSource CMS
// +----------------------------------------------------------------------
// | Copyright (c) 2014-2016 http://www.h1cms.com All rights reserved.
// | Copyright (c) 2014-2016 嘉兴领格信息技术有限公司，并保留所有权利。
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Allen <allen@lg4.cn>
// +----------------------------------------------------------------------

namespace getw;

/**
 * Description of View
 *
 */
class View {

    /**
     * 渲染模板
     * @param string $path 模板路径
     * @param array $_data 参数
     * @param bool $outputReturn 为true返回模板内容，false输出内容
     * @return string
     */
    public static function render($path, $_data = array(), $outputReturn = false) {
        $obLevel = ob_get_level();
        $error_level = error_reporting();
        if (config('config.debug', false)) {
            error_reporting(E_ALL ^ E_NOTICE);
        } else {
            error_reporting(0);
        }
        ob_start();
        extract($_data, EXTR_SKIP);
        try {
            include $path;
        } catch (Exception $e) {
            while (ob_get_level() > $obLevel) {
                ob_end_clean();
            }
            throw $e;
        }
        error_reporting($error_level);
        if ($outputReturn) {
            return ltrim(ob_get_clean());
        }
        echo ltrim(ob_get_clean());
    }

}
