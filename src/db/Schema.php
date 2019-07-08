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
 * Class Schema
 * @package getw\db
 */
class Schema {

    /**
     *
     * @var \PDO
     */
    public $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * @param $table
     * @return int
     */
    function tableExists($table) {
        $query = $this->db->query("SHOW TABLES LIKE '{$table}'");
        return $query->rowCount();
    }

    /**
     * @param $table
     * @return string
     */
    public static function parseTableName($table){
        if(is_string($table)){
            $table = explode(',',$table);
        }

        $tables = [];
        foreach ($table as $t){
            $table_alias = preg_split("/([\s]AS[\s])|(\s)/i",$t);
            $_tbname = isset($table_alias[0]) ? $table_alias[0]:NULL;
            $_tbname_alias = isset($table_alias[1]) ? $table_alias[1]:NULL;
            if(empty($_tbname_alias)){
                $tables[] = "{{$_tbname}}";
            }else{
                $tables[] = "{{$_tbname}} AS {$_tbname_alias}";
            }

        }
        return join(',',$tables);
    }

    /**
     * @param $columns
     * @return string
     */
    public static function parseColumns($columns){
        if(is_array($columns)){
            return join(',',$columns);
        }
        return $columns;
    }

    /**
     * @return string
     */
    public static function randParamName() {
        return ':' . \getw\Str::xrandom(\getw\Str::ALPHA);
    }
}
