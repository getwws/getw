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
 * Class Statement
 * @package getw\db
 */
class Statement extends \PDOStatement {

//    public $dbh;

    public function __destruct() {
        parent::closeCursor();
    }

//    protected function __construct($dbh) {
//        $this->dbh = $dbh;
//    }

    public function bindValue($parameter, $value, $data_type = PDO::PARAM_STR) {
        if (isset($parameter[0]) && $parameter[0] != ':') {
            $parameter = ':' . $parameter;
        }
        switch (gettype($value)) {
            case 'integer':
                $data_type = PDO::PARAM_INT;
                break;
            case 'unknown type':
            case 'string':
                $data_type = PDO::PARAM_STR;
                break;
            case 'NULL':
                $data_type = PDO::PARAM_NULL;
                break;
            case 'boolean':
                $data_type = PDO::PARAM_BOOL;
                break;
            case 'object':
                $data_type = PDO::PARAM_STR;
                break;
            default :
                $data_type = PDO::PARAM_STR;
                break;
        }
        parent::bindValue($parameter, $value, $data_type);
    }

    public function bindValues($params) {
        foreach ($params as $parameter => $value) {
            $this->bindValue($parameter, $value);
        }
    }

    public function bindParams($params) {
        foreach ($params as $parameter => $value) {
            $this->bindParam($parameter, $value);
        }
    }

    public function bindParam($parameter, &$variable, $data_type = PDO::PARAM_STR, $length = null, $driver_options = null) {
        if (isset($parameter[0]) && $parameter[0] != ':') {
            $parameter = ':' . $parameter;
        }
        switch (gettype($data_type)) {
            case 'integer':
                $data_type = PDO::PARAM_INT;
                break;
            case 'unknown type':
            case 'string':
                $data_type = PDO::PARAM_STR;
                break;
            case 'NULL':
                $data_type = PDO::PARAM_NULL;
                break;
            case 'boolean':
                $data_type = PDO::PARAM_BOOL;
                break;
            default :
                $data_type = PDO::PARAM_STR;
                break;
        }
        parent::bindParam($parameter, $variable, $data_type, $length, $driver_options);
    }

    public function execute($input_parameters = null) {
        if (is_array($input_parameters)) {
            foreach ($input_parameters as $parameter => $value) {
                $this->bindValue($parameter, $value);
            }
        }
        return parent::execute();
    }

}
