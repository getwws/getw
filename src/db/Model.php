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

/**
 * Class Model
 * @package getw\db
 */
abstract class Model implements \ArrayAccess {

    /**
     *
     * @var string tablename
     */
    protected $table;

    /**
     *
     * @var string primarykey
     */
    protected $primaryKey = 'id';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     *
     * @var array db field
     */
    protected $attributes = array();

    /**
     *
     * @var boolean newrecord
     */
    public $isNewRecord = false;
    protected static $instance = null;

    /**
     * Table Attributes
     * @param array $attributes
     */
    public function __construct(array $attributes = array()) {
        $this->fill($attributes);
    }

    /**
     * Set New Record
     * @param boolean $flag
     * @return \getw\Db\Model
     */
    public function setNewRecord($flag) {
        $this->isNewRecord = $flag;
        return $this;
    }

    public function getKeyName() {
        return $this->primaryKey;
    }

    public function getKeyValue() {
        return $this->getAttribute($this->getKeyName());
    }

    public function getTableName() {
        return $this->table;
    }

    public function setAttribute($key, $value) {
        $this->attributes[$key] = $value;
        return $this;
    }

    public function hasAttribute($key) {
        return isset($this->attributes[$key]);
    }

    public function getAttribute($key, $default = null) {
        if ($this->hasAttribute($key)) {
            return $this->attributes[$key];
        }
        return $default;
    }

    public function removeAttribute($key) {
        unset($this->attributes[$key]);
        return $this;
    }

    public function getAttributes() {
        return $this->attributes;
    }

    public function toJson() {
        return json_encode($this->attributes);
    }

    /**
     * 
     * @param string $connection
     * @return \getw\Db\Database
     */
    public function db($connection = NULL) {
        return Connection::getConenction($connection);
    }

    public function createQuery($connection = null) {
        return new QueryBuilder($this->table, $connection);
    }

    /**
     * 
     * @param string $connection
     * @return \getw\Db\QueryBuilder
     */
    public static function query($connection = null) {
        return new QueryBuilder(static::model()->table, $connection);
    }

    public static function make($attributes = []) {
        return new static($attributes);
    }

    public static function model() {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }
        return static::$instance;
    }

    public static function all() {
        $model = static::model();
        return $model->query()->get()->fetchAll(\PDO::FETCH_CLASS, get_called_class());
    }

    public static function findById($id) {
        $model = static::model();
        $stm = static::query()->whereIn($model->primaryKey, $id)->get();
        return $stm->fetchObject(get_called_class());
    }

    public static function findOne($conditions = []) {
        $query = static::query();
        if (is_array($conditions)) {
            while (list($statement, $operator, $params) = $conditions) {
                $query->where($statement, $operator, $params);
            }
        }
        return $query->get()->fetchObject(get_called_class());
    }

    public static function find($conditions = []) {
        $query = static::query();
        if (is_array($conditions)) {
            while (list($statement, $operator, $params) = $conditions) {
                $query->where($statement, $operator, $params);
            }
        }
        return $query->get()->fetchAll(\PDO::FETCH_CLASS, get_called_class());
    }

    public static function create($attributes = []) {
        $model = static::make($attributes);
        return $model->save();
    }

    public function save() {
        if ($this->isNewRecord) {
            $this->db()->insert($this->table, $this->attributes);
            $this->setNewRecord(false);
            $this->setAttribute($this->getKeyName(), $this->db()->lastInsertId());
            return $this->getKeyValue();
        } else {
            $this->db()->update($this->table, $this->attributes, [$this->getKeyName() => $this->getKeyValue()]);
        }
        return $this;
    }

    public function update(array $attributes = array()) {
        $this->fill($attributes)->save();
        return $this;
    }

    public function fill(array $attributes = array()) {
        foreach ($attributes as $key => $value) {
            $this->attributes[$key] = $value;
        }
        return $this;
    }

    public function destory() {
        return $this->db()->delete($this->table, [$this->getKeyName() => $this->getKeyValue()]);
    }

    public static function delete($ids) {
        $ids = is_array($ids) ? $ids : func_get_args();
        $model = new static;
        return $model->query()->whereIn($model->getKeyName(), $ids)->delete();
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key) {
        return $this->getAttribute($key);
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function __set($key, $value) {
        $this->setAttribute($key, $value);
    }

    /**
     * Determine if the given attribute exists.
     *
     * @param  mixed  $offset
     * @return bool
     */
    public function offsetExists($offset) {
        return $this->hasAttribute($offset);
    }

    /**
     * Get the value for a given offset.
     *
     * @param  mixed  $offset
     * @return mixed
     */
    public function offsetGet($offset) {
        return $this->getAttribute($offset);
    }

    /**
     * Set the value for a given offset.
     *
     * @param  mixed  $offset
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($offset, $value) {
        $this->setAttribute($offset, $value);
    }

    /**
     * Unset the value for a given offset.
     *
     * @param  mixed  $offset
     * @return void
     */
    public function offsetUnset($offset) {
        $this->removeAttribute($offset);
    }

    /**
     * Determine if an attribute exists on the model.
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset($key) {
        return isset($this->attributes[$key]);
    }

    /**
     * Unset an attribute on the model.
     *
     * @param  string  $key
     * @return void
     */
    public function __unset($key) {
        unset($this->attributes[$key]);
    }

}
