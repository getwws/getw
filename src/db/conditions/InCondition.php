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

namespace getw\db\conditions;


use getw\db\Schema;

class InCondition
{
    public static function build($name , $operator , $params)
    {
        if(!isset($params[0])){
            throw new \InvalidArgumentException("$name $operator 必须一个参数");
        }
        if(!isset($params[0]) || !is_array($params[0])){
            throw new \InvalidArgumentException("$name $operator 必须是一个数组");
        }
        $paramName = Schema::randParamName();
        $bindValues = [];
        $i = 0;
        foreach ($params[0] as $param){
            $bindValues[$paramName . $i] = $param;
            $i++;
        }
        $inNames = implode(',',array_keys($bindValues));
        return [
            "{$name} {$operator} ({$inNames})",
            $bindValues
        ];
    }
}