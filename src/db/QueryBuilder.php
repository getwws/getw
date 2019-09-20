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

/**
 * Class QueryBuilder
 * @package getw\db
 */
class QueryBuilder {

    public $table;
    protected $db;
    public $select = [];
    public $from = [];
    public $join = [];
    public $having = [];
    public $where = [];
    public $orderBy = [];
    public $groupBy = [];
    public $params = [];
    public $limit = '';

    public function __construct($table, $connection = null) {
        if (!empty($table)) {
            $this->table = $this->prepareTableName($table);
            $this->from[] = $this->table;
        }
        $this->db = Connection::getConenction($connection);
    }

    /**
     *
     * @param string $table
     * @param null|string $connection Db Name
     * @return QueryBuilder
     */
    public static function table($table, $connection = null) {
        return new QueryBuilder($table, $connection);
    }

    /**
     * Query Count
     * @example
     * #统计行数
     * echo QueryBuilder::table('name')->count();
     * @param string $column
     * @return mixed
     */
    public function count($column = '*') {
        $sql = "SELECT COUNT({$column}) AS rowcount FROM {{$this->table}} " . $this->prepareWhere();
        return $this->db->queryValue($sql);
    }

    /**
     * Query Delete
     *
     * @return bool
     */
    public function delete() {
        $sql = "DELETE FROM {$this->table} " . $this->prepareWhere();
        return $this->db->query($sql);
    }

    /**
     * Query Update
     * @param string $data
     * @return bool|int 返回行数或者false
     */
    public function update($data) {
        $placeholders = array();
        $input_params = array();
        foreach ($data as $name => $value) {
            if ($value instanceof Expr) {
                $placeholders[] = $this->quoteColumn($name) . '=' . $value->raw;
            } else {
                $placeholders[] = $this->quoteColumn($name) . '=:' . $name;
                $input_params[$name] = $value;
            }
        }
        $sql = "UPDATE {$this->table} " . ' SET ' . implode(', ', $placeholders) . $this->prepareWhere();
        if (($stm = $this->db->query($sql))) {
            return $stm->rowCount();
        }
        return false;
    }

    /**
     * @param string $data
     * @return int
     */
    public function insert($data) {
        return $this->db->insert($this->table, $data);
    }

    /**
     * @param $id
     * @param string $primaryKey
     * @return mixed
     */
    public function find($id, $primaryKey = 'id') {
        return $this->db->getRow("SELECT * FROM {$this->table} WHERE {$primaryKey}=:$primaryKey", [$primaryKey => $id]);
    }

    /**
     * Select
     *
     * @param string|array $columns 列名
     * @return $this
     */
    public function select($columns) {
        $this->select[] = $columns;
        return $this;
    }

    /**
     * Query Join
     *
     * @param string $table 表名
     * @param string $on Join ON
     * @param string $type Join Type(LEFT/RIGHT)
     * @return $this
     */
    public function join($table, $on, $type = 'LEFT JOIN') {
        $this->join[] = sprintf("%s %s ON %s", $type, $this->prepareTableName($table), $on);
        return $this;
    }

    /**
     * Query left join
     *
     * @param string $table 表名
     * @param string $on Join ON
     * @return $this
     */
    public function leftJoin($table, $on) {
        $this->join[] = sprintf("LEFT JOIN %s ON %s", $this->prepareTableName($table), $on);
        return $this;
    }

    /**
     * Query right join
     * @param string $table 表名
     * @param string $on Join ON
     * @return $this
     */
    public function rightJoin($table, $on) {
        $this->join[] = sprintf("RIGHT JOIN %s ON %s", $this->prepareTableName($table), $on);
        return $this;
    }

    /**
     * Query inner join
     * @param string $table Table Name
     * @param $on Join ON
     * @return $this
     */
    public function innerJoin($table, $on) {
        $this->join[] = sprintf("INNER JOIN %s ON %s", $this->prepareTableName($table), $on);
        return $this;
    }

    /**
     * Query From
     * @param string $table Table Name
     * @return $this
     */
    public function from($table) {
        $this->from[] = $this->prepareTableName($table);
        return $this;
    }

    /**
     * Group By
     * @example
     * DB::table()->groupBy('name');
     * @param $statement
     * @return $this
     */
    public function groupBy($statement) {
        $this->groupBy[] = $statement;
        return $this;
    }

    /**
     * Order By
     * @example
     * DB::table()->orderBy('name asc');
     * @param string $statement
     * @return $this
     */
    public function orderBy($statement) {
        $this->orderBy[] = $statement;
        return $this;
    }

    /**
     * Query Limit
     * @example
     * DB::table()->limit(10,0);
     * @param string $limit Limit
     * @param null|int $offset 偏移量
     * @return $this
     */
    public function limit($limit, $offset = null) {
        $this->limit = '';
        if (!is_null($offset)) {
            $this->limit = $offset . ', ';
        }
        $this->limit .= $limit;
        return $this;
    }

    /**
     * Query where
     * @example
     * DB::table()->where('name','=','Lisa');
     * @param string $statement 列名
     * @param string $operator 操作符(=,>=,<=,!=)
     * @param array $params
     * @return $this
     */
    public function where($statement, $operator = '=', $params = []) {
        $paramName = '';
        if (!is_null($params)) {
            $paramName = $this->db->randParamName();
            $this->bindValue($paramName, $params);
        }
        if (!empty($this->where)) {
            $this->where[] = ' AND ';
        }
        $this->where[] = sprintf("%s %s %s", $this->db->quoteColumn($statement), $operator, $paramName);
        return $this;
    }

    /**
     * Query orWhere
     * @param string $statement 列名
     * @param string $operator 操作符(=,>=,<=,!=)
     * @param array $params
     * @return $this
     */
    public function orWhere($statement, $operator = '=', $params = []) {
        $paramName = '';
        if (!is_null($params)) {
            $paramName = $this->db->randParamName();
            $this->bindValue($paramName, $params);
        }
        if (!empty($this->where)) {
            $this->where[] = ' OR ';
        }
        $this->where[] = sprintf("%s %s %s", $this->db->quoteColumn($statement), $operator, $paramName);
        return $this;
    }

    /**
     * Query Condition
     *
     * @example
     * DB::table('test')->select('*')->condition('name=:name',['name'=>'allen'],'AND / OR');
     * @param $statement
     * @param array $params
     * @param string $operator
     * @return $this
     */
    public function condition($statement, $params = [], $operator = 'AND') {
        if (!empty($this->where)) {
            $this->where[] = " $operator ";
        }
        $this->where[] = $statement;
        $this->addParams($params);
        return $this;
    }

    /**
     * Query whereIn
     *
     * @example
     * DB::table('test')->select('*')->whereIn('name',['lisa','allen'],'AND / OR');
     *
     * @param string $column 列名
     * @param array $params 参数
     * @param string $condition AND 或者 NO
     * @return $this
     */
    public function whereIn($column, $params, $condition = 'AND') {
        $this->prepareWhereIn($column, $params, '%s IN (%s)', $condition);
        return $this;
    }

    /**
     * Query whereNotIn
     *
     * @example
     * DB::table('test')->select('*')->whereIn('name',['lisa','allen'],'AND / OR');
     *
     * @param string $column 列名
     * @param array $params 参数
     * @param string $condition AND 或者 NO
     * @return $this
     */
    public function whereNotIn($column, $params, $condition = 'AND') {
        $this->prepareWhereIn($column, $params, '%s NOT IN (%s)', $condition);
        return $this;
    }

    /**
     * Query having
     * @param string $statement
     * @param array $params
     * @return $this
     */
    public function having($statement, $params = []) {
        $this->having[] = $statement;
        $this->addParams($params);
        return $this;
    }

    /**
     * Query execute
     * @return Statement
     */
    public function execute() {
        return $this->db->executeQuery($this);
    }

    /**
     * 获取所有参数
     * @return array
     */
    public function getParams() {
        return $this->params;
    }

    /**
     * Query Bind Value
     * 设置参数
     * @param string $name 参数名
     * @param mixed $value 参数值
     * @return $this
     */
    public function bindValue($name, $value) {
        $this->params[$name] = $value;
        return $this;
    }

    /**
     * Query Bind Values
     * 绑定数组参数
     * @param array $params 参数
     * @return $this
     */
    public function bindValues($params) {
        $this->addParams($params);
        return $this;
    }

    /**
     * 添加参数
     * @param array $params
     * @return $this
     */
    public function addParams($params) {
        if (is_null($params)) {
            return;
        }
        if (!is_array($params)) {
            $params = array($params);
        }
        $this->params = array_merge($this->params, $params);
        return $this;
    }

    /**
     * Execute Query
     * @param array $params
     * @return \getw\db\Statement
     */
    public function get($params = []) {
        if (!empty($this->params)) {
            $params = array_merge($params, $this->params);
        }
        return $this->db->query($this->getQuery(), $params);
    }

    /**
     * Clean Query
     */
    public function clean() {
        $this->select = [];
        $this->join = [];
        $this->where = [];
        $this->order = [];
        $this->groupBy = [];
        $this->having = [];
        $this->limit = '';
    }

    protected function prepareSelect() {
        if (empty($this->select)) {
            $this->select("*");
        }
        return "SELECT " . implode(", ", $this->select) . " FROM " . implode(", ", $this->from) . " ";
    }

    protected function prepareJoin() {
        if (!empty($this->join)) {
            return join(" ", $this->join) . " ";
        }
        return;
    }

    protected function prepareWhere() {
        if (!empty($this->where)) {
            return "WHERE " . implode(" ", $this->where) . " ";
        }
        return '';
    }

    protected function prepareHaving() {
        if (!empty($this->having)) {
            return "HAVING " . implode(", ", $this->having) . " ";
        }
        return '';
    }

    protected function prepareGroup() {
        if (!empty($this->groupBy)) {
            return "GROUP BY " . implode(", ", $this->groupBy) . " ";
        }
        return '';
    }

    protected function prepareOrder() {
        if (!empty($this->orderBy)) {
            return "ORDER BY " . implode(", ", $this->orderBy) . " ";
        }
        return '';
    }

    protected function prepareTableName($table) {        
        if (is_array($table)) {
            return sprintf('{%s} AS %s', \getw\Arr::get($table, 0), \getw\Arr::get($table, 1));
        } else if (isset($table[0]) && $table[0] == '{') {
            return $table;
        } else if (isset($table[0]) && $table[0] != '{') {
            return "{{$table}} as $table";
        }
    }

    protected function prepareWhereIn($column, $params, $format, $condition = 'AND') {
        if(!is_array($params)){
            $params = [$params];
        }
        if (!is_null($params)) {
            $paramName = $this->db->randParamName();
            $index = 0;
            $bindings = [];
            foreach ($params as $value) {
                $this->bindValue($paramName . $index, $value);
                $bindings[] = $paramName . $index;
                $index++;
            }
        }
        if (!empty($this->where)) {
            $this->where[] = " $condition ";
        }
        $this->where[] = sprintf($format, $this->db->quoteColumn($column), join(',', $bindings));
    }

    protected function prepareLimit() {
        if (!empty($this->limit)) {
            return "LIMIT " . $this->limit;
        }
        return '';
    }

    public function getQuery() {
        $sql = $this->prepareSelect();
        $sql .= $this->prepareJoin();
        $sql .= $this->prepareWhere();
        $sql .= $this->prepareGroup();
        $sql .= $this->prepareHaving();
        $sql .= $this->prepareOrder();
        $sql .= $this->prepareLimit();
        return $sql;
    }

}
