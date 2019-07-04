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

use PDO;

/**
 * Class Database
 * @package getw\db
 */
class Database extends PDO {

    protected $driver = 'mysql';
    protected $tablePrefix = '';
    protected $lastQueryString;

    public function __construct($dsn, $username, $password, $options) {
        parent::__construct($dsn, $username, $password, $options);
        $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, ["getw\\db\\Statement"]);
    }
    /**
     * 
     * @param string $statement
     * @param array $params
     * @return \getw\db\Statement
     */
    public function query($statement, $params = []) {        
        if (!is_array($params) && !is_null($params)) {
            $params = array($params);
        }
        $this->lastQueryString = $statement;
        $stm = $this->prepare($statement);
        if ($stm->execute($params)) {
            return $stm;
        }
        return false;
    }

    public function exec($statement) {
        $this->lastQueryString = $statement;
        return parent::exec($this->prepareSQL($statement));
    }

    /**
     * 
     * @param string $statement
     * @param array $params
     * @return \getw\db\Statement
     */
    public function statement($statement, $params = []) {
        $this->lastQueryString = $statement;
        $stm = $this->prepare($statement);
        if (is_array($params)) {
            $stm->bindValues($params);
        }
        return $stm;
    }

    /**
     * 
     * @param array $statement
     * @param array $driver_options
     * @return Statement
     */
    public function prepare($statement, $driver_options = []) {
        return parent::prepare($this->prepareSQL($statement), $driver_options);
    }

    /**
     * 
     * @param string $statement
     * @param array $params
     * @return \PDOStatement
     */
    public function raw_query($statement, $params = null) {
        $this->lastQueryString = $statement;
        $stm = parent::prepare($statement);
        if ($stm->execute($params)) {
            return $stm;
        }
        return FALSE;
    }

    public function fetchAll($statement, $params = null) {
        $stm = $this->query($statement, $params);
        return $stm->fetchAll(PDO::FETCH_OBJ);
    }

    public function getRow($statement, $params = null) {
        $stm = $this->query($statement, $params);
        return $stm->fetchObject();
    }

    public function getValue($statement, $params = array()) {
        return $this->query($statement, $params)->fetchColumn(0);
    }

    public function getCol($statement, $params = array()) {
        $stm = $this->prepare($statement);
        $stm->execute($params);
        return $stm->fetchAll(\PDO::FETCH_COLUMN, 0);
    }

    public function renameTable($oldName, $newName) {
        return $this->exec('RENAME TABLE ' . $this->quoteTable($oldName) . ' TO ' . $this->quoteTable($newName));
    }

    public function dropTable($table) {
        return $this->exec('DROP TABLE ' . $this->quoteTable($table));
    }

    public function truncateTable($table) {
        return $this->exec('TRUNCATE TABLE ' . $this->quoteTable($table));
    }

    public function insert($table, $data) {
        $params = array();
        $names = array();
        $placeholders = array();
        foreach ($data as $name => $value) {
            $names[] = $this->quoteColumn($name);
            if ($value instanceof Expr) {
                $placeholders[] = $value->raw;
            } else {
                $bindName = \getw\Str::xrandom(\getw\Str::ALPHA);
                $placeholders[] = ':' . $bindName;
                $params[$bindName] = $value;
            }
        }
        $sql = 'INSERT INTO ' . $this->quoteTable($table)
                . ' (' . implode(', ', $names) . ') VALUES ('
                . implode(', ', $placeholders) . ')';
        $this->lastQueryString = $sql;
        $statement = $this->prepare($sql);
        $statement->execute($params);
        return $this->lastInsertId();
    }

    public function update($table, $data, $conditions = '', $params = array()) {
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

        $sql = 'UPDATE ' . $this->quoteTable($table) . ' SET ' . implode(', ', $placeholders);
        if (($where = $this->prepareConditions($conditions, $params, $input_params)) != '') {
            $sql.=' WHERE ' . $where;
        }
        $this->lastQueryString = $sql;
        $statement = $this->prepare($sql);
        $statement->execute($input_params);
        return $statement->rowCount();
    }

    public function delete($table, $conditions = '', $params = array()) {
        $sql = 'DELETE FROM ' . $this->quoteTable($table);
        $input_params = array();
        if (($where = $this->prepareConditions($conditions, $params, $input_params)) != '') {
            $sql.=' WHERE ' . $where;
        }
        $this->lastQueryString = $sql;
        $statement = $this->prepare($sql);
        $statement->execute($input_params);
        return $statement->rowCount();
    }

    public function count($table, $conditions = '', $params = array()) {
        $sql = 'SELECT COUNT(*) as rowcount FROM ' . $this->quoteTable($table);
        $input_params = array();
        if (($where = $this->prepareConditions($conditions, $params, $input_params)) != '') {
            $sql.=' WHERE ' . $where;
        }
        $this->lastQueryString = $sql;
        $statement = $this->prepare($sql);
        $statement->execute($input_params);
        return $statement->fetchColumn(0);
    }

    public function getAssoc($table, $columns, $conditions = '', $params = array()) {
        if (is_array($columns)) {
            $columns = join(',', array_map(function($value) {
                        return $this->quoteColumn($value);
                    }, $columns));
        }
        $sql = 'SELECT ' . $columns . ' FROM ' . $this->quoteTable($table);
        $input_params = array();
        if (($where = $this->prepareConditions($conditions, $params, $input_params)) != '') {
            $sql.=' WHERE ' . $where;
        }
        $this->lastQueryString = $sql;
        $statement = $this->prepare($sql);
        $statement->execute($input_params);
        $keypairs = [];
        while ($row = $statement->fetch(PDO::FETCH_NUM)) {
            $keypairs[$row[0]] = $row[1];
        }
        unset($statement);
        return $keypairs;
    }

    public function createQuery($connection = null) {
        return new QueryBuilder(NULL, $connection);
    }

    public function executeQuery(QueryBuilder $query) {
        return $this->query($query->getQuery(), $query->getParams());
    }

    function getSchema() {
        return new Schema($this);
    }

    private function prepareConditions($conditions, $params = array(), &$input_params) {
        if (is_array($conditions)) {
            $lines = array();
            $i = 0;
            foreach ($conditions as $name => $value) {
                if ($value instanceof Expr) {
                    $lines[] = $this->quoteColumn($name) . '=' . $value->raw;
                } else {
                    $bindName = \getw\Str::xrandom(\getw\Str::ALPHA);
                    $lines[] = $this->quoteColumn($name) . '=:' . $bindName . $i;
                    $input_params[$bindName . $i] = $value;
                }
                $i++;
            }
            return implode(' AND ', $lines);
        } else if (is_string($conditions) && is_array($params) && is_assoc($params)) {
            foreach ($params as $name => $value) {
                if ($value instanceof Expr) {
                    $input_params[$name] = $value->raw;
                }
                $input_params[$name] = $value;
            }
            return $conditions;
        } else if (is_string($conditions) && ( is_scalar($params) || (is_array($params) && !is_assoc($params)))) {
            if (is_scalar($params)) {
                $params = array($params);
            }
            $input_params += $params;
            return $conditions;
        }
        return '';
    }

    public function prepareSQL($sql) {
        return preg_replace_callback(
                '/(\\{(%?[\w\-\. ]+%?)\\}|\\[([\w\-\. ]+)\\])/',
                function ($matches) {
            if (isset($matches[3])) {
                return $this->quoteColumn($matches[3]);
            } else {
                return $this->quoteTable($matches[2]);
            }
        }, $sql
        );
    }

    public function quoteTable($table) {
        $table = $this->tablePrefix . $table;
        switch ($this->driver) {
            case 'mysql':
            case 'mariadb':
                return '`' . $table . '`';
            case 'mssql':
                return "[$table]";
            case 'pssql':
                return '"' . $table . '"';
            default:
                return $table;
        }
    }

    public function quoteColumn($columnName) {
        $colAlias = explode('.', $columnName);
        if (is_array($colAlias) && count($colAlias) == 2) {
            return $this->quoteColumn($colAlias[0]) . '.' . $this->quoteColumn($colAlias[1]);
        }
        switch ($this->driver) {
            case 'mysql':
            case 'mariadb':
                return "`$columnName`";
            case 'mssql':
                return "[$columnName]";
            case 'pssql':
                return '"' . $columnName . '"';
            default:
                return $columnName;
        }
    }

    public function getTableName($name) {
        return $this->tablePrefix . $name;
    }

    public function setPrefix($prefix) {
        $this->tablePrefix = $prefix;
    }

    public function setDriver($driver) {
        $this->driver = $driver;
    }

    public function getLastQuery() {
        return $this->lastQueryString;
    }

    public function info() {
        $output = array(
            'host' => 'SERVER_INFO',
            'driver' => 'DRIVER_NAME',
            'client' => 'CLIENT_VERSION',
            'version' => 'SERVER_VERSION',
            'connection' => 'CONNECTION_STATUS'
        );
        foreach ($output as $key => $value) {
            $output[$key] = $this->getAttribute(constant('PDO::ATTR_' . $value));
        }
        return $output;
    }

    public function randParamName() {
        return ':' . \getw\Str::xrandom(\getw\Str::ALPHA);
    }

}
