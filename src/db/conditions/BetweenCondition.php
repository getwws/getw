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

namespace getw\db\conditions;


use getw\db\Schema;

class BetweenCondition
{
    public static function build($name , $operator , $params)
    {
        if(!isset($params[0],$params[1])){
            throw new \InvalidArgumentException("$name $operator:  BETWEEN VALUE1 AND VALUE2 ");
        }

        $paramName1 = Schema::randParamName();
        $paramName2 = Schema::randParamName();


        return [
            "{$name} {$operator} {$paramName1} AND {$paramName2}",
            [
                $paramName1 => $params[0],
                $paramName2 => $params[1]
            ]
        ];
    }
}