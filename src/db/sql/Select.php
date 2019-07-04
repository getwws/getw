<?php
// +----------------------------------------------------------------------
// | getw
// +----------------------------------------------------------------------
// | Copyright (c) 2014-2016 http://www.getw.cn All rights reserved.
// | Copyright (c) 2014-2016 嘉兴领格信息技术有限公司，并保留所有权利。
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Allen <allen@getw.cn>
// +----------------------------------------------------------------------


namespace getw\db\sql;


use getw\Arr;
use getw\DB;
use getw\db\Operators;
use getw\db\Schema;

/**
 * Class Select
 * @package getw\db\sql
 */
class Select
{
    public $table;
    public $columns;
    public $join = [];
    public $where = [];
    public $limit;
    public $orderBy;
    public $groupBy;
    public $params = [];

    public function __construct($table, $columns)
    {
        $this->table = Schema::parseTableName($table);
        $this->columns = Schema::parseColumns($columns);
    }

    public function from($table){
        $this->table = Schema::parseTableName($table);
        return $this;
    }

    public function select($columns = '*'){
        $this->columns = Schema::parseColumns($columns);
        return $this;
    }

    protected function __join($type, $table, $on)
    {
        $table = Schema::parseTableName($table);
        $this->join[] = "$type $table ON $on";
    }

    public function leftJoin($table, $on)
    {
        $this->__join('LEFT JOIN', $table, $on);
        return $this;
    }

    public function rightJoin($table, $on)
    {
        $this->__join('RIGHT JOIN', $table, $on);
        return $this;
    }

    public function distinct()
    {
        //SELECT
        $sql = "SELECT DISTINCT  {$this->columns} FROM {$this->table} ";
        return DB::query($sql);
    }

    /**
     * @param $condition
     * @param array $params
     * @return $this
     */
    public function where($condition, $params = [])
    {
        if (is_string($condition) && is_array($params)) {
            $this->__where('AND', $condition, $params);
        } else if(is_string($condition) && !is_array($params)){
            $paramName = Schema::randParamName();
            $this->__where('AND', "$condition=$paramName", [$paramName => $params]);
        } else if (is_array($condition) && empty($params)) {
            $this->__createConditionFromArray($condition,'AND');
        }
        return $this;
    }

    public function orWhere($condition, $params = [])
    {
        if (is_string($condition) && is_array($params)) {
            $this->__where('OR', $condition, $params);
        } else if(is_string($condition) && !is_array($params)){
            $paramName = Schema::randParamName();
            $this->__where('OR', "$condition=$paramName", [$paramName => $params]);
        } else if (is_array($condition) && empty($params)) {
            $this->__createConditionFromArray($condition,'OR');
        }
        return $this;
    }

    protected function __where($cOperator, $condition, $params = [])
    {
        if (!empty($this->where)) {
            $this->where[] = $cOperator;
        }
        $this->where[] = $condition;
        if (!empty($params)) {
            $this->addParams($params);
        }
    }

    /**
     *
     * @param $condition
     */
    protected function __createConditionFromArray($condition,$operator = 'AND')
    {
        //HASH FORMAT
        if (is_string(key($condition))) {
            foreach ($condition as $name => $value) {
                $paramName = Schema::randParamName();
                $this->__where($operator, "$name=$paramName", [$paramName => $value]);
            }

            return;
        }
        $first = Arr::get($condition, 0 , NULL);
        //[ARRAY/ARRAY]
        if(is_array($first)){
            foreach ($condition as $arrFromat){
                $this->__createConditionFromArrayFormat($arrFromat,$operator);
            }
        }else if(is_string($first)){ // [KEY/OPERATOR/VALUE|ARRAY]
            $this->__createConditionFromArrayFormat($condition,$operator);
        }

        return;
    }

    protected function __createConditionFromArrayFormat($condition,$operator = 'AND'){
        $len = count($condition);
        if($len == 1 && is_string($condition)){
            $this->__where($operator,$condition);
            return;
        } else if($len == 2){
            $name = Arr::get($condition, 0 , NULL);
            $value = Arr::get($condition, 1 , NULL);
            $paramName = Schema::randParamName();
            $this->__where($operator, "$name=$paramName", [$paramName => $value]);
            return;
        } else if($len >= 3){
            $name = Arr::get($condition, 0 , NULL);
            $c = Arr::get($condition, 1 , '');
            if(!Operators::isOperator($c)){
                throw new \Exception("不支持此操作符 [{$c}]");
            }
            $className = 'getw\db\conditions\SimpleCondition';
            $classpartial = str_replace(' ','',$c);
            if(class_exists('getw\db\conditions\\' . $classpartial . 'Condition')){
                $className = 'getw\db\conditions\\' . $classpartial . 'Condition';
            }

            $params = array_slice($condition,2);
            list($c,$p) = $className::build($name,$c,$params);
            $this->__where($operator, $c, $p);
        }
    }


    public function limit($limit, $offset = null)
    {
        if (!is_null($offset)) {
            $this->limit = $offset . ', ';
        }
        $this->limit .= $limit;
        return $this;
    }

    public function orderBy($condition)
    {
        $this->orderBy = $condition;
        return $this;
    }

    public function groupBy($condition)
    {
        $this->groupBy = $condition;
        return $this;
    }

    public function addParams($params)
    {
        $this->params = array_merge($this->params, $params);
        return $this;
    }

    /**
     * @return \getw\db\Statement
     */
    public function query($params = [])
    {
        $this->params = array_merge($this->params, $params);
        $query = DB::query($this->sql(), $this->params);
        return $query;
    }

    /**
     * Count
     * @param array $params
     * @return int
     */
    public function count($params = [])
    {
        $this->params = array_merge($this->params, $params);
        $query = DB::query($this->sql(), $this->params);
        return $query->fetchColumn(0);
    }


    public function sql()
    {
        //SELECT
        $sql = "SELECT {$this->columns} FROM {$this->table} ";
        //JOIN
        if (!empty($this->join)) {
            $sql .= join(' ', $this->join);
        }
        //WHERE
        if (!empty($this->where)) {
            $sql .= ' WHERE ' . join(' ', $this->where);
        }
        //ORDER BY
        if (!empty($this->orderBy)) {
            $sql .= ' ORDER BY ' . $this->orderBy;
        }
        //GROUP BY
        if (!empty($this->groupBy)) {
            $sql .= ' GROUP BY ' . $this->groupBy;
        }
        //LIMIT
        if (!empty($this->limit)) {
            $sql .= ' LIMIT ' . $this->limit;
        }

        return $sql;
    }
}