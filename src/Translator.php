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

use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Translator as SymfonyTranslator;

/**
 * Class Translator
 * @package getw
 */
class Translator
{

    /**
     *
     * @var \Symfony\Component\Translation\Translator
     */
    public static $translator;

    public static function create($locale)
    {
        static::$translator = new SymfonyTranslator($locale, new MessageSelector());
        static::$translator->addLoader('php', new \Symfony\Component\Translation\Loader\PhpFileLoader());
    }

    /**
     *
     * @return \Symfony\Component\Translation\Translator
     */
    public static function instance()
    {
        if (is_null(static::$translator)) {
            static::create('zh_CN');
        }
        return static::$translator;
    }

}
