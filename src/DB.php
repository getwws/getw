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
     * DB::table
     *
     * @example
     * DB::table('name');
     *
     * @param string $table Table Name
     * @param null|string $connection Connection Name
     * @return QueryBuilder
     */
    public static function table($table, $connection = null)
    {
        return new QueryBuilder($table, $connection);
    }


    /**
     * DB::query
     *
     * @example
     * DB::query('select * from table',['key'=>'value']);
     * @param string $statement SQL String
     * @param null|array $params 参数
     * @param null $connection
     * @return db\Statement
     * @throws \Exception
     */
    public static function query($statement, $params = null, $connection = null)
    {
        return Connection::getConenction($connection)->query($statement, $params);
    }


    /**
     * @param $statement
     * @param null $params
     * @param null $connection
     * @return db\Statement
     * @throws \Exception
     */
    public static function statement($statement, $params = null, $connection = null)
    {
        return Connection::getConenction($connection)->statement($statement, $params);
    }

    /**
     * @param $statement
     * @param null $params
     * @param null $connection
     * @return \PDOStatement
     * @throws \Exception
     */
    public static function rawQuery($statement, $params = null, $connection = null)
    {
        return Connection::getConenction($connection)->raw_query($statement, $params);
    }

    /**
     * @param $statement
     * @param null $connection
     * @return int
     * @throws \Exception
     */
    public static function exec($statement, $connection = null)
    {
        return Connection::getConenction($connection)->exec($statement);
    }

    /**
     * @param $statement
     * @param array $driver_options
     * @param null $connection
     * @return db\Statement
     * @throws \Exception
     */
    public static function prepare($statement, $driver_options = [], $connection = null)
    {
        return Connection::getConenction($connection)->prepare($statement, $driver_options);
    }

    /**
     * @param $table
     * @param $data
     * @param null $connection
     * @return int|string
     * @throws \Exception
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
     * @throws \Exception
     */
    public static function update($table, $data, $conditions = '', $params = array())
    {
        return Connection::getConenction()->update($table, $data, $conditions, $params);
    }

    /**
     * @param $table
     * @param string $conditions
     * @param array $params
     * @param null $connection
     * @return int
     * @throws \Exception
     */
    public static function delete($table, $conditions = '', $params = array(), $connection = null)
    {
        return Connection::getConenction($connection)->delete($table, $conditions, $params);
    }

    /**
     * DB::count
     *
     * @example
     * DB::count('table','name=:name',['name'=>'lisa']);
     * @param string $table Table Name
     * @param string $conditions
     * @param array $params
     * @param null $connection
     * @return int|mixed
     * @throws \Exception
     */
    public static function count($table, $conditions = '', $params = [], $connection = null)
    {
        return Connection::getConenction($connection)->count($table, $conditions, $params);
    }


    public static function beginTransaction($connection = null)
    {
        Connection::getConenction($connection)->beginTransaction();
    }


    public static function commit($connection = null)
    {
        Connection::getConenction($connection)->commit();
    }


    public static function rollBack($connection = null)
    {
        Connection::getConenction($connection)->rollBack();
    }


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
     * 获取Last Insert ID
     * @param null|string $name 默认为空
     * @param null|string $connection 连接名称
     * @return int 最后插入的ID
     * @throws \Exception
     */
    public static function lastInsertId($name = null, $connection = null)
    {
        return Connection::getConenction($connection)->lastInsertId($name);
    }

    /**
     * DB::getRow
     *
     * @param string $statement Sql String
     * @param array $params 参数
     * @param null|string $connection 连接名称
     * @return Object|null 返回行
     * @throws \Exception
     */
    public static function getRow($statement = null, $params = [], $connection = null)
    {
        return Connection::getConenction($connection)->getRow($statement, $params);
    }


    /**
     * DB::getValue
     *
     * 获取一个值
     *
     * @param string $statement Sql String
     * @param array $params 参数
     * @param null $connection 连接名称
     * @return mixed|int|string
     * @throws \Exception
     */
    public static function getValue($statement = null, $params = [], $connection = null)
    {
        return Connection::getConenction($connection)->getValue($statement, $params);
    }

    /**
     * DB::getCol
     *
     * 获取第一列的所有数据
     *
     * @example
     *
     * $names = DB::getCol("select name from users");
     *
     * @param string $statement Sql String
     * @param array $params 参数
     * @param null $connection 连接名称
     * @return array 返回第一列的所有数据
     * @throws \Exception
     */
    public static function getCol($statement = null, $params = [], $connection = null)
    {
        return Connection::getConenction($connection)->getCol($statement, $params);
    }

    /**
     * DB::getAll
     *
     *
     * @param string $statement Sql String
     * @param array $params 参数
     * @param null $connection 连接名称
     * @return mixed
     * @throws \Exception
     */
    public static function getAll($statement = null, $params = [], $connection = null)
    {
        return Connection::getConenction($connection)->fetchAll($statement, $params);
    }

    /**
     * DB::fetchAll
     *
     *
     * @param string $statement Sql String
     * @param array $params 参数
     * @param null $connection 连接名称
     * @return mixed
     * @throws \Exception
     */
    public static function fetchAll($statement = null, $params = [], $connection = null)
    {
        return Connection::getConenction($connection)->fetchAll($statement, $params);
    }

    /**
     * DB::getAssoc
     *
     * 获取一组键值对(第一列为key，第二列为value)
     *
     * @param string $table 表名
     * @param string $columns 列名
     * @param string $conditions 条件
     * @param array $params 参数
     * @param null $connection 连接名称
     * @return array
     * @throws \Exception
     */
    public static function getAssoc($table, $columns, $conditions = '', $params = array(), $connection = null)
    {
        return Connection::getConenction($connection)->getAssoc($table, $columns, $conditions, $params);
    }

    /**
     * DB::select
     *
     * @example
     * DB::select('table','*')->where();
     * @param string $table Table Name
     * @param string $columns Columns
     * @return Select
     */
    public static function select($table,$columns = '*'){
        return new Select($table,$columns);
    }

    /**
     * PDO
     *
     * @param null|string $connection 数据库连接名称
     * @return db\Database
     * @throws \Exception
     */
    public static function pdo($connection = null)
    {
        return Connection::getConenction($connection);
    }

    /**
     * @param null|string $connection 数据库连接名称
     * @return db\Database|mixed
     * @throws \Exception
     */
    public static function connection($connection = null)
    {
        return Connection::getConenction($connection);
    }

    /**
     * Last Query String
     * @param null|string $connection 数据库连接名称
     * @return string
     * @throws \Exception
     */
    public static function lastQuery($connection = null){
        return Connection::getConenction($connection)->getLastQuery();
    }


}
