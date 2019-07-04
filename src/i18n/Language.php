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

namespace getw\i18n;

/**
 * Class Language
 * @package getw\i18n
 */
class Language {

    public static $languages = [];
    public static $language = 'zh_CN';
    public static $languagePath = [];

    public static function with($key, $value = null) {
        if (is_array($key)) {
            static::$languages = array_merge(static::$languages, $key);
        } else {
            static::$languages[$key] = $value;
        }
    }

    public static function set($key, $value) {
        static::$languages[$key] = $value;
    }

    public static function get($key) {
        if (isset(static::$languages[$key])) {
            return static::$languages[$key];
        }
        return $key;
    }

    public static function has($key) {
        return array_key_exists($key, static::$languages);
    }

    public static function loadLanguage($name) {
        if (isset(static::$languages['load.records']) && array_key_exists($name, static::$languages['load.records'])) {
            return true;
        }
        if (is_array(static::$languagePath)) {
            foreach (static::$languagePath as $path) {
                $file = $path . '/' . $name . '.php';
                if (file_exists($file)) {
                    include($file);
                    if (!empty($_LANG) && is_array($_LANG)) {
                        static::$languages+=$_LANG;
                    }
                }
            }
        }
    }

    public static function setLanguage($language) {       
        static::$language = $language;       
        static::$languagePath[] = realpath(ROOT_PATH . '/storage/languages/');        
        Language::loadLanguage($language);
    }
    
    public static function addSearchPath($path) {                  
        static::$languagePath[] = realpath($path);      
    }

    public static function getLanguagePaths() {
        return static::$languagePath;
    }

}
