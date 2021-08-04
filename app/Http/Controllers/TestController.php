<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TestController extends Controller
{

    /**
     * @name   GET请求
     * @header string|true               $token              header头
     * @param  string|true               $str                字符串
     * @param  int|true                  $number             数字
     * @param  array|false               $arr                数组
     * @return array
     */
    public function test1(Request $request)
    {
        $ret = [
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'header' => [
                    'token' => $request->header('token')
                ],
                'body' => $request->all(),
            ],
        ];
        if (!empty($ret['data']['body']['number'])) {
            $ret['data']['body']['number'] = intval($ret['data']['body']['number']);
        }


        return $ret;
    }

    /**
     * @name   GET请求
     * @header string|true               $token              header头
     * @param  string|true               $str                字符串
     * @param  int|true                  $number             数字
     * @param  array|false               $arr                数组
     * @return array
     */
    public function test2(Request $request)
    {
        $ret = [
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'header' => [
                    'token' => $request->header('token')
                ],
                'body' => $request->all(),
            ],
        ];
        if (!empty($ret['data']['body']['number'])) {
            $ret['data']['body']['number'] = intval($ret['data']['body']['number']);
        }


        return $ret;
    }

}
