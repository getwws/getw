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

namespace getw\db;


use getw\DB;
use getw\db\sql\Select;

abstract class ActiveRecord
{

    protected static $_instance;
    public static function db($connection = null)
    {
        return DB::connection($connection);
    }

    public static function findBySql($sql, $params = [])
    {
        return static::db()->query($sql, $params);
    }

    /**Find by id
     * @param array $params
     * @return mixed
     */
    public static function findById($params = [])
    {
        if(!is_array($params)){
            $params = [$params];
        }
        return static::select()->where([static::primaryKey(),'IN',$params])->query()->fetchObject(get_called_class());
    }

    public static function count(Select $query, $columns = 'count(*) as rowcount')
    {
        $defaultColumns = $query->columns;
        $total = $query->select($columns)->count();
        $query->select($defaultColumns);
        return $total;
    }

    public static function getAll(Select $select = null, $params = [])
    {
        if(is_null($select)){
            $select = static::find();
        }
        return $select->query($params);
    }

    public static function select($columns = '*')
    {
        return new Select(static::tableName(),$columns);
    }

    public static function find($columns = '*')
    {
        return new Select(static::tableName(),$columns);
    }



    final public static function model()
    {
        if (is_null(static::$_instance)) {
            static::$_instance = new static;
        }
        return static::$_instance;
    }
}