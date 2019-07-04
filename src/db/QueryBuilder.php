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

    public static function table($table, $connection = null) {
        return new QueryBuilder($table, $connection);
    }

    public function count($column = '*') {
        $sql = "SELECT COUNT({$column}) AS rowcount FROM {{$this->table}} " . $this->prepareWhere();
        return $this->db->queryValue($sql);
    }

    public function delete() {
        $sql = "DELETE FROM {{$this->table}} " . $this->prepareWhere();
        return $this->db->query($sql);
    }

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
        $sql = "UPDATE {{$this->table}} " . ' SET ' . implode(', ', $placeholders) . $this->prepareWhere();
        if (($stm = $this->db->query($sql))) {
            return $stm->rowCount();
        }
        return false;
    }

    public function insert($data) {
        return $this->db->insert($this->table, $data);
    }

    public function find($id, $primaryKey = 'id') {
        return $this->db->getRow("SELECT * FROM {$this->table} WHERE {$primaryKey}=:$primaryKey", [$primaryKey => $id]);
    }

    public function select($columns) {
        $this->select[] = $columns;
        return $this;
    }

    public function join($table, $on, $type = 'LEFT JOIN') {
        $this->join[] = sprintf("%s %s ON %s", $type, $this->prepareTableName($table), $on);
        return $this;
    }

    public function leftJoin($table, $on) {
        $this->join[] = sprintf("LEFT JOIN %s ON %s", $this->prepareTableName($table), $on);
        return $this;
    }

    public function rightJoin($table, $on) {
        $this->join[] = sprintf("RIGHT JOIN %s ON %s", $this->prepareTableName($table), $on);
        return $this;
    }

    public function innerJoin($table, $on) {
        $this->join[] = sprintf("INNER JOIN %s ON %s", $this->prepareTableName($table), $on);
        return $this;
    }

    public function from($table) {
        $this->from[] = $this->prepareTableName($table);
        return $this;
    }

    public function groupBy($statement) {
        $this->groupBy[] = $statement;
        return $this;
    }

    public function orderBy($statement) {
        $this->orderBy[] = $statement;
        return $this;
    }

    public function limit($limit, $offset = null) {
        $this->limit = '';
        if (!is_null($offset)) {
            $this->limit = $offset . ', ';
        }
        $this->limit .= $limit;
        return $this;
    }

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

    public function condition($statement, $params = [], $operator = 'AND') {
        if (!empty($this->where)) {
            $this->where[] = " $operator ";
        }
        $this->where[] = $statement;
        $this->addParams($params);
        return $this;
    }

    public function whereIn($column, $params, $condition = 'AND') {
        $this->prepareWhereIn($column, $params, '%s IN (%s)', $condition);
        return $this;
    }

    public function whereNotIn($column, $params, $condition = 'AND') {
        $this->prepareWhereIn($column, $params, '%s NOT IN (%s)', $condition);
        return $this;
    }

    public function having($statement, $params = []) {
        $this->having[] = $statement;
        $this->addParams($params);
        return $this;
    }

    //--------------------------------------------------
    public function execute() {
        return $this->db->executeQuery($this);
    }

    public function getParams() {
        return $this->params;
    }

    public function bindValue($name, $value) {
        $this->params[$name] = $value;
        return $this;
    }

    public function bindValues($params) {
        $this->addParams($params);
        return $this;
    }

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
     * 
     * @param type $params
     * @return \getw\db\Statement
     */
    public function get($params = []) {
        if (!empty($this->params)) {
            $params = array_merge($params, $this->params);
        }
        return $this->db->query($this->getQuery(), $params);
    }

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
