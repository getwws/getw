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


class Operators
{
    public static $operators = [
        'AND',
        '&&',
        '=',
        ':=',
        'BETWEEN',
        'BINARY',
        '&',
        '~',
        '|',
        '^',
        'CASE',
        'DIV',
        '/',
        '=',
        '<=>',
        '>',
        '>=',
        'IS',
        'IS NOT',
        'IS NOT NULL',
        'IS NULL',
        '->',
        '->>',
        '<<',
        '<',
        '<=',
        'LIKE',
        '-',
        '%, MOD',
        'NOT',
        '!',
        'NOT BETWEEN',
        '!=, <>',
        'NOT LIKE',
        'NOT REGEXP',
        '||, OR',
        '+',
        'REGEXP',
        '>>',
        'RLIKE',
        'SOUNDS LIKE',
        '*',
        '-',
        'XOR',
        'IN',
        'NOT IN'

    ];

    public static function isOperator($operator){
        return in_array(strtoupper($operator),static::$operators);
    }
}