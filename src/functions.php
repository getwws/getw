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

/**
 * @constant GETW_VERSION getw版本
 */
define('GETW_VERSION', '1.0.2');

/**
 * @constant DS 目录分隔符
 */
define('DS', DIRECTORY_SEPARATOR);

/**
 * @param $name Key名称
 * @param null $default 默认值为null
 * @return array|string 返回配置
 */
function config($name, $default = null)
{
    return \getw\Config::get($name, $default);
}

/***
 * @return bool CLI环境返回true
 */
function is_cli()
{
    return (php_sapi_name() === 'cli');
}

/**
 * 检测数组是否是KeyPairs
 * @param array $arr
 * @return bool 如果是ASSOC返回true
 */
function is_assoc($arr)
{
    return (is_array($arr) && isset($arr[0]) && is_string($arr));
}

/**
 * @param $method method类型（GET/POST/PUT..）
 * @return bool 测试HTTP METHOD
 */
function isMethod($method)
{
    $_method = filter_var(\getw\Arr::get($_REQUEST, '_method', $_SERVER['REQUEST_METHOD']), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
    return strtolower($_method) == strtolower($method);
}


/**
 * @param $value
 * @param null $default
 * @return null
 */
function get_default($value, $default = NULL)
{
    if (!empty($value)) {
        return $value;
    }
    return $default;
}

function value($value)
{
    return $value instanceof Closure ? $value() : $value;
}

// +----------------------------------------------------------------------
// | Flash Helpers
// +----------------------------------------------------------------------
function add_flash($message, $type = getw\Session::INFO)
{
    \getw\Session::instance()->addFlash($message, $type);
}

function get_flash($type = null, $default = [])
{
    return \getw\Session::instance()->getFlash($type, $default);
}

function has_flash($type = null)
{
    return \getw\Session::instance()->hasFlash($type);
}

/**
 *
 * @return \getw\Session
 */
function session()
{
    return \getw\Session::instance();
}

function set_session($name, $value)
{
    \getw\Session::instance()->set($name, $value);
}

function get_session($name, $default = null)
{
    return \getw\Session::instance()->get($name, $default);
}

function has_session($name)
{
    return \getw\Session::instance()->has($name);
}

function remove_session($name)
{
    return \getw\Session::instance()->remove($name);
}

function input_get($name, $default = null)
{
    return \getw\Input::get($name, $default);
}

function input_post($name, $default = null)
{
    return \getw\Input::post($name, $default);
}

function input($name, $default = null)
{
    return \getw\Input::all($name, $default);
}

function system_maintenance()
{
    if (config('config.maintenance', false)) {
        return;
    }
    header('Content-Type: text/html; charset=utf-8');
    header('Retry-After: 600');
    $content = '系统正在维护，请稍后在访问';
    ?>
    <!DOCTYPE html>
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title><?php echo '系统维护'; ?></title>

    </head>
    <body>
    <h1><?php echo $content; ?></h1>
    </body>
    </html>
    <?php
    die();
}

function bootstrap($action = 'action')
{
    $action = filter_var(\getw\Arr::get($_REQUEST, $action, 'index'), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
    $_method = filter_var(\getw\Arr::get($_REQUEST, '_method', $_SERVER['REQUEST_METHOD']), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
    if (function_exists('init')) {
        init();
    }
    if (function_exists($action . 'Action')) {
        $function = $action . 'Action';
        $function();
    } else if (function_exists($action . $_method)) {
        $function = $action . $_method;
        $function();
    } else if (function_exists('do' . $_method)) {
        $function = 'do' . $_method;
        $function();
    }
    if (function_exists('destory')) {
        destory();
    }
}

// +----------------------------------------------------------------------
// | i18N
// +----------------------------------------------------------------------
function __($message, $args = [], $options = [])
{
    return \getw\I18n\Translation::translate($message, $args, $options);
}

function t($message, $args = [], $options = [])
{
    return \getw\I18n\Translation::translate($message, $args, $options);
}

function load_language($name)
{
    return \getw\I18n\Language::loadLanguage($name);
}

function trans($id, array $parameters = array(), $domain = null, $locale = null)
{
    return \getw\Translator::instance()->trans($id, $parameters, $domain, $locale);
}

function transChoice($id, $number, array $parameters = array(), $domain = null, $locale = null)
{
    return \getw\Translator::instance()->transChoice($id, $number, $parameters, $domain, $locale);
}

//$available_languages = array("en", "zh-cn", "es");
//
//$langs = prefered_language($available_languages, $_SERVER["HTTP_ACCEPT_LANGUAGE"]);
function prefered_language(array $available_languages, $http_accept_language)
{

    $available_languages = array_flip($available_languages);

//    $langs;
    preg_match_all('~([\w-]+)(?:[^,\d]+([\d.]+))?~', strtolower($http_accept_language), $matches, PREG_SET_ORDER);
    foreach ($matches as $match) {

        list($a, $b) = explode('-', $match[1]) + array('', '');
        $value = isset($match[2]) ? (float)$match[2] : 1.0;

        if (isset($available_languages[$match[1]])) {
            $langs[$match[1]] = $value;
            continue;
        }

        if (isset($available_languages[$a])) {
            $langs[$a] = $value - 0.1;
        }
    }
    arsort($langs);

    return $langs;
}

function response($content = '', $statusCode = 200, $headers = array())
{
    return new \getw\Response($content, $statusCode, $headers);
}

function response_json($content = '', $statusCode = 200, $headers = array())
{
    return \getw\Response::toJson($content, $statusCode, $headers);
}

function redirect($url = '', $status = 302, $headers = array())
{
    foreach ($headers as $key => $value) {
        header("{$key}:{$value}");
    }
    header('Location: ' . $url, true, $status);
    exit();
}

// +----------------------------------------------------------------------
// | XSS
// +----------------------------------------------------------------------
function html_filter($html, $config = null)
{
    return HTMLPurifier::instance()->purify($html, $config);
}

function html_escape($var, $double_encode = TRUE)
{
    if (empty($var)) {
        return $var;
    }
    if (is_array($var)) {
        foreach (array_keys($var) as $key) {
            $var[$key] = html_escape($var[$key], $double_encode);
        }
        return $var;
    }
    return htmlspecialchars($var, ENT_QUOTES, config_item('charset'), $double_encode);
}

function is_ssl()
{
    if (isset($_SERVER['HTTPS'])) {
        if ('on' == strtolower($_SERVER['HTTPS'])) {
            return true;
        }
        if ('1' == $_SERVER['HTTPS']) {
            return true;
        }
    } elseif (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) {
        return true;
    }
    return false;
}

/**
 *
 * @param  string $connection
 * @return \getw\db\Database
 */
function db($connection = null)
{
    return \getw\DB::connection($connection);
}

/**
 *
 * @param  string $table
 * @param  string $connection
 * @return \getw\Db\QueryBuilder
 */
function db_table($table, $connection = null)
{
    return \getw\DB::table($table, $connection);
}

/**
 * Get a schema builder instance.
 *
 * @param  string $connection
 * @return \getw\db\Schema
 */
function db_schema($connection = null)
{
    return \getw\DB::connection($connection)->getSchema();
}

function db_query($statement, $params = [], $connection = null)
{
    return \getw\DB::connection($connection)->query($statement, $params);
}

function db_exec($statement, $connection = null)
{
    return \getw\DB::connection($connection)->exec($statement);
}

function db_prepare($statement, $driver_options = [], $connection = null)
{
    return \getw\DB::connection($connection)->prepare($statement, $driver_options);
}

function db_fetchValue($statement, $params = [], $connection = null)
{
    return \getw\DB::connection($connection)->getValue($statement, $params);
}

function db_fetchCol($statement, $params = [], $connection = null)
{
    return \getw\DB::connection($connection)->getCol($statement, $params);
}

function db_fetchAll($statement, $params = [], $connection = null)
{
    $stm = \getw\DB::connection($connection)->query($statement, $params);
    return $stm->fetchAll(\PDO::FETCH_OBJ);
}

function db_fetch($statement, $params = [], $connection = null)
{
    $stm = \getw\DB::connection($connection)->query($statement, $params);
    return $stm->fetch(\PDO::FETCH_OBJ);
}

function db_fetchPairs($statement, $params = [], $connection = null)
{
    $stm = \getw\DB::connection($connection)->query($statement, $params);
    return $stm->fetchAll(\PDO::FETCH_KEY_PAIR);
}

function db_fetchAssoc($statement, $params = [], $connection = null)
{
    $stm = \getw\DB::connection($connection)->query($statement, $params);
    return $stm->fetchAll(\PDO::FETCH_KEY_PAIR);
}

function db_prepare_array($data, $field = null)
{
    if ($field == null) {
        $field = \getw\DB::connection()->randParamName();
    }

    $index = 0;
    $bindings = [];

    foreach ($data as $value) {
        $bindings[$field . $index] = $value;
        $index++;
    }
    return $bindings;
}

function db_insert($table, $data, $connection = null)
{
    return \getw\DB::connection($connection)->insert($table, $data);
}

function db_update($table, $data, $conditions = '', $params = array(), $connection = null)
{
    return \getw\DB::connection($connection)->update($table, $data, $conditions, $params);
}

function db_delete($table, $conditions = '', $params = array(), $connection = null)
{
    return \getw\DB::connection($connection)->delete($table, $conditions, $params);
}

function array_find_keys($keys, &$array)
{
    $r = [];
    foreach ($keys as $key) {
        if (isset($array[$key])) {
            $r[$key] = $array[$key];
        }
    }
    return $r;
}

function array_unset_keys($keys, &$array)
{
    foreach ($keys as $key) {
        if (isset($array[$key])) {
            unset($array[$key]);
        }
    }
    return $array;
}

//date
function format_date($timestamp, $format = '', $timezone = NULL, $default = '')
{
    if (empty($timestamp)) {
        return $default;
    }
    switch ($format) {
        case 'short':
            $format = __('date_format_short', 'Y-m-d');
            break;
        case 'long':
            $format = __('date_format_long', 'Y-m-d H:i:s');
            break;
        case '':
            $format = __('date_format_short', 'Y-m-d');
            break;
    }
    if (is_null($timezone)) {
        //date()
        return date($format, $timestamp);
    }
    $date_time = date_create('@' . $timestamp);
    if (!isset($timezone)) {
        $timezone = date_default_timezone_get();
    }
    date_timezone_set($date_time, new DateTimeZone($timezone));
    return date_format($date_time, $format);
}

if (!function_exists('http_response_code')) {

    function http_response_code($code = NULL)
    {

        if ($code !== NULL) {

            switch ($code) {
                case 100:
                    $text = 'Continue';
                    break;
                case 101:
                    $text = 'Switching Protocols';
                    break;
                case 200:
                    $text = 'OK';
                    break;
                case 201:
                    $text = 'Created';
                    break;
                case 202:
                    $text = 'Accepted';
                    break;
                case 203:
                    $text = 'Non-Authoritative Information';
                    break;
                case 204:
                    $text = 'No Content';
                    break;
                case 205:
                    $text = 'Reset Content';
                    break;
                case 206:
                    $text = 'Partial Content';
                    break;
                case 300:
                    $text = 'Multiple Choices';
                    break;
                case 301:
                    $text = 'Moved Permanently';
                    break;
                case 302:
                    $text = 'Moved Temporarily';
                    break;
                case 303:
                    $text = 'See Other';
                    break;
                case 304:
                    $text = 'Not Modified';
                    break;
                case 305:
                    $text = 'Use Proxy';
                    break;
                case 400:
                    $text = 'Bad Request';
                    break;
                case 401:
                    $text = 'Unauthorized';
                    break;
                case 402:
                    $text = 'Payment Required';
                    break;
                case 403:
                    $text = 'Forbidden';
                    break;
                case 404:
                    $text = 'Not Found';
                    break;
                case 405:
                    $text = 'Method Not Allowed';
                    break;
                case 406:
                    $text = 'Not Acceptable';
                    break;
                case 407:
                    $text = 'Proxy Authentication Required';
                    break;
                case 408:
                    $text = 'Request Time-out';
                    break;
                case 409:
                    $text = 'Conflict';
                    break;
                case 410:
                    $text = 'Gone';
                    break;
                case 411:
                    $text = 'Length Required';
                    break;
                case 412:
                    $text = 'Precondition Failed';
                    break;
                case 413:
                    $text = 'Request Entity Too Large';
                    break;
                case 414:
                    $text = 'Request-URI Too Large';
                    break;
                case 415:
                    $text = 'Unsupported Media Type';
                    break;
                case 500:
                    $text = 'Internal Server Error';
                    break;
                case 501:
                    $text = 'Not Implemented';
                    break;
                case 502:
                    $text = 'Bad Gateway';
                    break;
                case 503:
                    $text = 'Service Unavailable';
                    break;
                case 504:
                    $text = 'Gateway Time-out';
                    break;
                case 505:
                    $text = 'HTTP Version not supported';
                    break;
                default:
                    exit('Unknown http status code "' . htmlentities($code) . '"');
                    break;
            }

            $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');

            header($protocol . ' ' . $code . ' ' . $text);

            $GLOBALS['http_response_code'] = $code;
        } else {

            $code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);
        }

        return $code;
    }

}

function array_get($array, $key, $default = null)
{
    return \getw\Arr::get($array, $key, $default);
}

function array_has($array, $keys)
{
    return \getw\Arr::has($array, $keys);
}

function array_last($array, callable $callback = null, $default = null)
{
    return \getw\Arr::last($array, $callback, $default);
}

function array_set(&$array, $key, $value)
{
    return \getw\Arr::set($array, $key, $value);
}

function array_where($array, callable $callback)
{
    return \getw\Arr::where($array, $callback);
}

if (!function_exists('e')) {
    function e($value)
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false);
    }

}

if (!function_exists('starts_with')) {

    function starts_with($haystack, $needles)
    {
        return \getw\Str::startsWith($haystack, $needles);
    }

}
if (!function_exists('ends_with')) {
    function ends_with($haystack, $needles)
    {
        return \getw\Str::endsWith($haystack, $needles);
    }

}

if (!function_exists('preg_replace_array')) {

    /**
     * Replace a given pattern with each value in the array in sequentially.
     *
     * @param  string $pattern
     * @param  array $replacements
     * @param  string $subject
     * @return string
     */
    function preg_replace_array($pattern, array $replacements, $subject)
    {
        return preg_replace_callback($pattern,
            function () use (&$replacements) {
                foreach ($replacements as $key => $value) {
                    return array_shift($replacements);
                }
            }, $subject);
    }

}

if (!function_exists('object_get')) {
    function object_get($object, $key, $default = null)
    {
        if (is_null($key) || trim($key) == '') {
            return $object;
        }
        foreach (explode('.', $key) as $segment) {
            if (!is_object($object) || !isset($object->{$segment})) {
                return value($default);
            }
            $object = $object->{$segment};
        }
        return $object;
    }

}

if (!function_exists('ifOr')) {
    function ifOr($condition, $one = NULL, $two = NULL)
    {
        if (!empty($condition)) {
            return $one;
        } else {
            return $two;
        }
    }
}

function str_limit($value, $limit = 100, $end = '...')
{
    return \getw\Str::limit($value, $limit, $end);
}

function str_random($length = 16)
{
    return \getw\Str::random($length);
}

function str_replace_array($search, array $replace, $subject)
{
    return \getw\Str::replaceArray($search, $replace, $subject);
}

function str_slug($title, $separator = '-')
{
    return \getw\Str::slug($title, $separator);
}

