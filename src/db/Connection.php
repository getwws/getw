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

namespace getw\db;

use Exception;
use getw\Config;
use PDO;

/**
 * Class Connection
 * @package getw\db
 */
class Connection {

    protected static $_connections = [];

    public function __construct() {
        
    }

    /**
     * 获取数据库连接
     *
     * @param null|string $connection 连接名称
     * @return Database
     * @throws Exception
     */
    public static function getConenction($connection = null) {
        if (is_null($connection)) {
            $connection = Config::get('database.default');
        }
        if(isset(static::$_connections[$connection])){
            return static::$_connections[$connection];
        }
        $config = Config::get('database.connections.' . $connection, []);
        if (empty($config) || !isset($config['driver'])) {
            throw new Exception("Error::Connection::getConnection() 请先配置数据库连接文件");
        }
        $opt = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
        ];
        try {
            switch ($config['driver']) {
                case 'mysql':
                    $host = \getw\Arr::get($config, 'host', 'localhost');
                    $dbname = \getw\Arr::get($config, 'database', 'localhost');
                    $username = \getw\Arr::get($config, 'username', 'root');
                    $password = \getw\Arr::get($config, 'password', 'root');
                    $port = \getw\Arr::get($config, 'port', '3306');
                    $prefix = \getw\Arr::get($config, 'prefix', '');
                    $unix_socket = \getw\Arr::get($config, 'unix_socket', false);
                    if ($unix_socket !== false) {
                        $port = ";unix_socket=$unix_socket";
                    } else {
                        $port = ";port=$port";
                    }
                    $charset = \getw\Arr::get($config, 'charset', 'utf8');
//                $opt[PDO::MYSQL_ATTR_INIT_COMMAND] = $charset; 
                    $dsn = "mysql:host=$host;dbname={$dbname}{$port};charset=$charset";
                    $db = new Database($dsn, $username, $password, $opt);
                    $db->setPrefix($prefix);
                    $db->setDriver('mysql');
                    static::$_connections[$connection] = $db;
                    return $db;
                default:
                    throw new Exception("[{$config['driver']}]数据库驱动类型不支持");
            }
        } catch (PDOException $e) {
            die("ERROR: Could not connect. " . $e->getMessage());
        }
    }

}
