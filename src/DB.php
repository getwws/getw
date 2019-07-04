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

use getw\db\Connection;
use getw\db\QueryBuilder;
use getw\db\sql\Select;

/**
 * Class DB
 * @package getw
 */
class DB
{

    /**
     * @param string $table Table Name
     * @param string $connection null连接默认链接
     * @return QueryBuilder
     */
    public static function table($table, $connection = null)
    {
        return new QueryBuilder($table, $connection);
    }

    /**
     *
     * @param string $statement SQL字符串
     * @param array $params 参数KEY=>VALUE
     * @param string $connection
     * @return \getw\db\Statement
     */
    public static function query($statement, $params = null, $connection = null)
    {
        return Connection::getConenction($connection)->query($statement, $params);
    }

    /**
     *
     * @param string $statement
     * @param array $params
     * @param string $connection
     * @return \getw\db\Statement
     */
    public static function statement($statement, $params = null, $connection = null)
    {
        return Connection::getConenction($connection)->statement($statement, $params);
    }

    /**
     *
     * @param string $statement
     * @param array $params
     * @param string $connection
     * @return \PDOStatement
     */
    public static function rawQuery($statement, $params = null, $connection = null)
    {
        return Connection::getConenction($connection)->raw_query($statement, $params);
    }

    /**
     *
     * @param string $statement
     * @param string $connection
     * @return boolean
     */
    public static function exec($statement, $connection = null)
    {
        return Connection::getConenction($connection)->exec($statement);
    }

    /**
     *
     * @param string $statement
     * @param array $driver_options
     * @param string $connection
     * @return \getw\db\Statement
     */
    public static function prepare($statement, $driver_options = [], $connection = null)
    {
        return Connection::getConenction($connection)->prepare($statement, $driver_options);
    }

    /**
     * @param string $table
     * @param array $data
     * @param string $connection
     * @return string
     */
    public function insert($table, $data, $connection = null)
    {
        return Connection::getConenction($connection)->insert($table, $data);
    }

    /**
     * @param $table
     * @param $data
     * @param string $conditions
     * @param array $params
     * @return int
     */
    public static function update($table, $data, $conditions = '', $params = array())
    {
        return Connection::getConenction()->update($table, $data, $conditions, $params);
    }

    /**
     * @param $table
     * @param string $conditions
     * @param array $params
     * @param string $connection
     * @return int
     */
    public static function delete($table, $conditions = '', $params = array(), $connection = null)
    {
        return Connection::getConenction($connection)->delete($table, $conditions, $params);
    }

    /**
     *
     * @param string $table
     * @param string $conditions
     * @param array $params
     * @param string $connection
     * @return int
     */
    public static function count($table, $conditions = '', $params = [], $connection = null)
    {
        return Connection::getConenction($connection)->count($table, $conditions, $params);
    }

    /**
     * @param string $connection
     */
    public static function beginTransaction($connection = null)
    {
        Connection::getConenction($connection)->beginTransaction();
    }

    /**
     * @param string $connection
     */
    public static function commit($connection = null)
    {
        Connection::getConenction($connection)->commit();
    }

    /**
     * @param string $connection
     */
    public static function rollBack($connection = null)
    {
        Connection::getConenction($connection)->rollBack();
    }

    /**
     * @param \Closure $callable
     * @param string $connection
     * @return bool
     */
    public static function transaction($callable, $connection = null)
    {
        if (is_callable($callable)) {
            $db = Connection::getConenction($connection);
            $db->beginTransaction();
            $result = $callable($db);
            if ($result === false) {
                $db->rollBack();
            } else {
                $db->commit();
            }
            return $result;
        } else {
            return false;
        }
    }

    /**
     * @param string $name
     * @param string $connection
     * @return string
     */
    public static function lastInsertId($name = null, $connection = null)
    {
        return Connection::getConenction($connection)->lastInsertId($name);
    }

    /**
     * @param string $statement
     * @param array $params
     * @param string $connection
     * @return mixed
     */
    public static function getRow($statement = null, $params = [], $connection = null)
    {
        return Connection::getConenction($connection)->getRow($statement, $params);
    }

    /**
     * @param string $statement
     * @param array $params
     * @param string $connection
     * @return mixed
     */
    public static function getValue($statement = null, $params = [], $connection = null)
    {
        return Connection::getConenction($connection)->getValue($statement, $params);
    }

    public static function getCol($statement = null, $params = [], $connection = null)
    {
        return Connection::getConenction($connection)->getCol($statement, $params);
    }

    public static function getAll($statement = null, $params = [], $connection = null)
    {
        return Connection::getConenction($connection)->getAll($statement, $params);
    }

    public static function getAssoc($table, $columns, $conditions = '', $params = array(), $connection = null)
    {
        return Connection::getConenction($connection)->getAssoc($table, $columns, $conditions, $params);
    }

    /**
     * @param string|array $columns
     * @return Select
     */
    public static function select($table,$columns = '*'){
        return new Select($table,$columns);
    }

    /**
     *
     * @param string $connection
     * @return \getw\db\Database PDO
     */
    public static function pdo($connection = null)
    {
        return Connection::getConenction($connection);
    }

    /**
     *
     * @param string $connection
     * @return \getw\db\Database PDO
     */
    public static function connection($connection = null)
    {
        return Connection::getConenction($connection);
    }

    /**
     * @param string $connection
     * @return mixed
     */
    public static function lastQuery($connection = null){
        return Connection::getConenction($connection)->getLastQuery();
    }


}
