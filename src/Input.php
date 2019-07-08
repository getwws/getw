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
 * Class Input
 * @package getw
 */
class Input {

    /**
     * Get the public ip address of the user.
     *
     * @param   string $default
     * @return  array|string
     */
    public static function ip($default = '0.0.0.0') {
        return static::server('REMOTE_ADDR', $default);
    }

    /**
     * Input Get
     * @param string $name
     * @param null|mixed $default
     * @return mixed
     */
    public static function get($name, $default = null) {
        return Arr::get($_GET, $name, $default);
    }

    /**
     * Input Post
     * @param string $name
     * @param null|mixed $default
     * @return mixed
     */
    public static function post($name, $default = null) {
        return Arr::get($_POST, $name, $default);
    }

    /**
     * Input All
     * @param string $name
     * @param null|mixed $default
     * @return mixed
     */
    public static function all($name = null, $default = null) {
        if ($name == null && $default == null) {
            return $_REQUEST;
        }
        return Arr::get($_REQUEST, $name, $default);
    }

    /**
     * 获取真实IP地址
     * @staticvar type $server_keys
     * @param type $default
     * @param type $exclude_reserved
     * @return type
     */
    public static function realIpAddress($default = '0.0.0.0', $exclude_reserved = false) {
        static $server_keys = null;
        if (empty($server_keys)) {
            $server_keys = array('HTTP_CLIENT_IP', 'REMOTE_ADDR', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_X_FORWARDED_FOR');
        }
        foreach ($server_keys as $key) {
            if (!static::server($key)) {
                continue;
            }
            $ips = explode(',', static::server($key));
            array_walk($ips, function (&$ip) {
                $ip = trim($ip);
            });
            $ips = array_filter($ips,
                    function($ip) use($exclude_reserved) {
                return filter_var($ip, FILTER_VALIDATE_IP, $exclude_reserved ? FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE : null);
            });
            if ($ips) {
                return reset($ips);
            }
        }
        return $default;
    }

    /**
     * Return's the protocol that the request was made with
     *
     * @return  string
     */
    public static function protocol() {
        if (static::server('HTTPS') == 'on' or
                static::server('HTTPS') == 1 or
                static::server('SERVER_PORT') == 443 or
                static::server('HTTP_X_FORWARDED_PROTO') == 'https' or
                static::server('HTTP_X_FORWARDED_PORT') == 443) {
            return 'https';
        }
        return 'http';
    }

    /**
     * Return's whether this is an AJAX request or not
     *
     * @return  bool
     */
    public static function isAjax() {
        return (static::server('HTTP_X_REQUESTED_WITH') !== null) and strtolower(static::server('HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest';
    }

    /**
     * Return's the referrer
     *
     * @param   string $default
     * @return  string
     */
    public static function referrer($default = '') {
        return static::server('HTTP_REFERER', $default);
    }

    /**
     * Return's the user agent
     *
     * @param   $default
     * @return  string
     */
    public static function user_agent($default = '') {
        return static::server('HTTP_USER_AGENT', $default);
    }

    /**
     * Fetch an item from the FILE array
     *
     * @param   string  $index    The index key
     * @param   mixed   $default  The default value
     * @return  string|array
     */
    public static function file($index = null, $default = null) {
        return (func_num_args() === 0) ? $_FILES : Arr::get($_FILES, $index, $default);
    }

    /**
     * 从$_COOKIE中获取cookie
     *
     * @param    string  $index    索引 (如果为空返回全部)
     * @param    mixed   $default  默认值
     * @return   string|array
     */
    public static function cookie($index = null, $default = null) {
        return (func_num_args() === 0) ? $_COOKIE : Arr::get($_COOKIE, $index, $default);
    }

    /**
     * Fetch an item from the SERVER array
     *
     * @param   string  $index    The index key
     * @param   mixed   $default  The default value
     * @return  string|array
     */
    public static function server($index = null, $default = null) {
        return (func_num_args() === 0) ? $_SERVER : Arr::get($_SERVER, strtoupper($index), $default);
    }

    /**
     * Fetch a item from the HTTP request headers
     *
     * @param   mixed $index
     * @param   mixed $default
     * @return  array
     */
    public static function headers($index = null, $default = null) {
        static $headers = null;
        // do we need to fetch the headers?
        if ($headers === null) {
            // deal with fcgi or nginx installs
            if (!function_exists('getallheaders')) {
                foreach (static::server() as $key => $value) {
                    if (Str::startsWith($key, 'HTTP_')) {
                        $key = join('-', array_map('ucfirst', explode('_', strtolower($key))));
                        $headers[$key] = $value;
                    }
                }
                $value = static::server('Content_Type', static::server('Content-Type')) and $headers['Content-Type'] = $value;
                $value = static::server('Content_Length', static::server('Content-Length')) and $headers['Content-Length'] = $value;
            } else {
                $headers = getallheaders();
            }
        }
        return empty($headers) ? $default : ((func_num_args() === 0) ? $headers : Arr::get(array_change_key_case($headers), strtolower($index), $default));
    }

    /**
     * query string from $_SERVER
     * @param string $default
     * @return array|string
     */
    public static function query_string($default = '') {
        return static::server('QUERY_STRING', $default);
    }

    /**
     * HTTP isMethod
     * @param string $method
     * @return bool
     */
    public static function isMethod($method = '') {
        return (static::server('REQUEST_METHOD') == strtolower($method));
    }

    /**
     * Get Method
     * @return mixed
     */
    public static function method() {
        if($_SERVER['REQUEST_METHOD'] == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)){
            $HTTP_X_HTTP_METHOD = $_SERVER['HTTP_X_HTTP_METHOD'];
            if ($HTTP_X_HTTP_METHOD == 'DELETE' || $HTTP_X_HTTP_METHOD == 'PUT') {
                return $HTTP_X_HTTP_METHOD;
            }
        } else if($_SERVER['REQUEST_METHOD'] == 'POST' && array_key_exists('_METHOD', $_POST)){
            $HTTP_X_HTTP_METHOD = $_POST['_METHOD'];
            if ($HTTP_X_HTTP_METHOD == 'DELETE' || $HTTP_X_HTTP_METHOD == 'PUT') {
                return $HTTP_X_HTTP_METHOD;
            }
        }
        return $_SERVER['REQUEST_METHOD'];
    }
}
