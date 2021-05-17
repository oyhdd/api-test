<?php

if (!function_exists('cut_substr')) {
    /**
     * 显示指定长度的字符串，超出长度以省略号(...)填补尾部显示
     * @ str 字符串
     * @ len 指定长度
     **/
    function cut_substr($str, int $len = 45)
    {
        if ($len % 3 != 0) {
            $len = intval($len / 3) * 3 + 3;
        }
        if (strlen($str) > $len) {
            $str = substr($str, 0, $len) . '...';
        }
        return $str;
    }
}

if (!function_exists('encrypt_str')) {
    /**
     * 字符串加*处理
     * @param    string                   $str         字符串
     * @param    integer                  $first_lenth 保留前n位
     * @param    integer                  $end_lenth   保留后n位
     * @return   string
     */
    function encrypt_str($str, $first_lenth = 1, $end_lenth = 1)
    {
        if (empty($str)) {
            return $str;
        }
        $strlen   = mb_strlen($str, 'utf-8');
        $firstStr = mb_substr($str, 0, $first_lenth, 'utf-8');
        $lastStr  = mb_substr($str, -$end_lenth, $end_lenth, 'utf-8');
        return $strlen <= 2 ? $firstStr . str_repeat('*', mb_strlen($str, 'utf-8') - 1) : $firstStr . str_repeat("*", $strlen - $first_lenth - $end_lenth) . $lastStr;
    }
}

if (!function_exists('unset_null')) {
    //递归方式把数组或字符串 null转换为空''字符串
    function unset_null($arr)
    {
        if ($arr !== null) {
            if (is_array($arr)) {
                if (!empty($arr)) {
                    foreach ($arr as $key => $value) {
                        if ($value === null) {
                            $arr[$key] = '';
                        } else {
                            $arr[$key] = unset_null($value); //递归再去执行
                        }
                    }
                }
            } else {
                if ($arr === null) { //注意三个等号
                    $arr = '';
                }
            }
        } else {
            $arr = '';
        }

        return $arr;
    }
}
