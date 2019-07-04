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

use ArrayAccess;

/**
 * Class Container
 * @package getw
 */
class Container implements ArrayAccess {

    /**
     * All of the instances set on the container.
     *
     * @var array
     */
    protected $instances = [];

    /**
     * aliase instances 
     * @var array
     */
    protected $aliases = [];

    /**
     * Container instances
     * @var Container 
     */
    public static $instance;

    /**
     * Create a new fluent container instances.
     *
     * @param  array|object    $instances
     * @return void
     */
    public function __construct($instances = []) {
        foreach ($instances as $key => $value) {
            $this->instances[$key] = $value;
        }
    }

    /**
     * Get an attribute from the container.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function get($key, $default = null) {
        if (array_key_exists($key, $this->instances)) {
            return $this->instances[$key];
        }

        return value($default);
    }

    public function set($key, $value) {
        $this->instances[$key] = $value;
    }

    public function has($key) {
        return array_key_exists($key, $this->instances);
    }

    /**
     * Get the instances from the container.
     *
     * @return array
     */
    public function getInstances() {
        return $this->instances;
    }

    /**
     * Convert the Fluent instances to an array.
     *
     * @return array
     */
    public function toArray() {
        return $this->instances;
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize() {
        return $this->toArray();
    }

    /**
     * Convert to JSON.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0) {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Determine if the given offset exists.
     *
     * @param  string  $offset
     * @return bool
     */
    public function offsetExists($offset) {
        return isset($this->{$offset});
    }

    /**
     * Get the value for a given offset.
     *
     * @param  string  $offset
     * @return mixed
     */
    public function offsetGet($offset) {
        return $this->{$offset};
    }

    /**
     * Set the value at the given offset.
     *
     * @param  string  $offset
     * @param  mixed   $value
     * @return void
     */
    public function offsetSet($offset, $value) {
        $this->{$offset} = $value;
    }

    /**
     * Unset the value at the given offset.
     *
     * @param  string  $offset
     * @return void
     */
    public function offsetUnset($offset) {
        unset($this->{$offset});
    }

    /**
     * Handle dynamic calls to the container to set instances.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return $this
     */
    public function __call($method, $parameters) {
        $this->instances[$method] = count($parameters) > 0 ? $parameters[0] : true;

        return $this;
    }

    /**
     * Dynamically retrieve the value of an attribute.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key) {
        return $this->get($key);
    }

    /**
     * Dynamically set the value of an attribute.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function __set($key, $value) {
        $this->instances[$key] = $value;
    }

    /**
     * Dynamically check if an attribute is set.
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset($key) {
        return isset($this->instances[$key]);
    }

    /**
     * Dynamically unset an attribute.
     *
     * @param  string  $key
     * @return void
     */
    public function __unset($key) {
        unset($this->instances[$key]);
    }

    public function flush() {
        $this->instances = [];
    }

    public function make($abstract, array $parameters = []) {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }
        $reflect = new \ReflectionClass($abstract);
        $object = $reflect->newInstanceArgs($parameters);
        $this->instances[$abstract] = $object;
        return $object;
    }

    /**
     * 
     * @return \getw\Container
     */
    public static function getInstance() {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }
        return static::$instance;
    }

    public static function setInstance($container = null) {
        return static::$instance = $container;
    }

}
