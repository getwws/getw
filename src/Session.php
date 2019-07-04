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
 * Class Session
 * @package getw
 */
class Session
{

    const INFO = 'info';
    const SUCCESS = 'success';
    const WARNING = 'warning';
    const ERROR = 'error';
    const defaultType = self::INFO;

    /**
     *
     * @var \getw\Session
     */
    private static $instance;

    /**
     *
     * @return \getw\Session
     */
    public static function instance()
    {
        if (is_null(static::$instance)) {
            if (!session_id() && !headers_sent()) {
                session_start();
            }
            static::$instance = new Session();
        }
        return static::$instance;
    }


    public function info($message)
    {
        return $this->addFlash($message, self::INFO);
    }

    public function success($message)
    {
        return $this->addFlash($message, self::SUCCESS);
    }


    public function warning($message)
    {
        return $this->addFlash($message, self::WARNING);
    }


    public function error($message)
    {
        return $this->addFlash($message, self::ERROR);
    }


    public function addFlash($message, $type = self::defaultType)
    {
        if (!isset($message[0]))
            return false;
        if (!isset($_SESSION['flash_messages']))
            $_SESSION['flash_messages'][$type] = array();
        $_SESSION['flash_messages'][$type][] = $message;
        return $this;
    }

    public function set($name, $value = null)
    {
        return $_SESSION[$name] = $value;
    }

    public function get($name, $default = null)
    {
        if (isset($_SESSION[$name])) {
            return $_SESSION[$name];
        }
        return $default;
    }

    public function has($name)
    {
        return empty($_SESSION[$name]) ? false : true;
    }

    public function remove($name)
    {
        if (is_array($name)) {
            foreach ($name as $key) {
                unset($_SESSION[$key]);
            }
        } else {
            unset($_SESSION[$name]);
        }
    }


    public function hasErrors()
    {
        return empty($_SESSION['flash_messages'][self::ERROR]) ? false : true;
    }


    public function hasFlash($type = null)
    {
        if (!is_null($type)) {
            if (!empty($_SESSION['flash_messages'][$type]))
                return $_SESSION['flash_messages'][$type];
        } else {
            foreach ([self::ERROR, self::INFO, self::WARNING, self::SUCCESS] as $type) {
                if (isset($_SESSION['flash_messages'][$type]) && !empty($_SESSION['flash_messages'][$type]))
                    return $_SESSION['flash_messages'][$type];
            }
        }
        return false;
    }


    public function getFlash($type = null)
    {
        $flash = [];
        if ($type == NULL && isset($_SESSION['flash_messages'])) {
            $flash = $_SESSION['flash_messages'];
            unset($_SESSION['flash_messages']);
        } else if (isset($_SESSION['flash_messages'][$type])) {
            $flash = $_SESSION['flash_messages'][$type];
            unset($_SESSION['flash_messages'][$type]);
        }
        return $flash;
    }


    public function clearFlash($types = [])
    {
        if ((is_array($types) && empty($types)) || is_null($types) || !$types) {
            unset($_SESSION['flash_messages']);
        } elseif (!is_array($types)) {
            $types = [$types];
        }
        foreach ($types as $type) {
            unset($_SESSION['flash_messages'][$type]);
        }
        return $this;
    }
}
