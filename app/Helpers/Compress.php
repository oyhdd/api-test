<?php

namespace App\Helpers;

/**
 * 数据解压缩
 */
class Compress
{

    /**
     * 数据压缩
     *
     * gzcompress 速度最快，压缩比率较高。
     * gzdeflate 压缩比率最高，速度稍慢于gzcompress
     * gzencode 与 gzdeflate 比较接近，gzdeflate稍有优势
     * bzcompress 速度最慢，压缩比率最慢。
     *
     * 建议使用 gzcompress 和 gzdeflate。
     *
     * @param    string                   $str 待压缩数据
     * @param    string                   $way 压缩算法
     * @return   string
     */
    public static function compress($str, $way = 'gzcompress'){
        $func_compress = array('gzcompress', 'gzencode', 'gzdeflate', 'bzcompress');
        $mstr = '';
        if (in_array($way, $func_compress)) {
            switch($way){
                case 'gzcompress':
                    $mstr = gzcompress($str, 9); // 解压方法：gzuncompress
                    break;

                case 'gzencode':
                    $mstr = gzencode($str, 9); // 解压方法：gzdecode php>=5.4
                    break;

                case 'gzdeflate':
                    $mstr = gzdeflate($str, 9); // 解压方法：gzinflate
                    break;

                case 'bzcompress':
                    $mstr = bzcompress($str, 9); // 解压方法：bzdecompress
                    break;

                default:
                    return false;
                    break;
            }
        } else {
            return false;
        }

        //转为utf-8
        $fileType = mb_detect_encoding($mstr , array('UTF-8','GBK','LATIN1','BIG5'));
        if($fileType != 'UTF-8'){
            $mstr = mb_convert_encoding($mstr ,'utf-8' , $fileType);
        }
        return base64_encode($mstr);
    }

    /**
     * 解压缩
     * @param    string                   $str 待解压数据
     * @param    string                   $way 压缩算法
     * @return   string
     */
    public static function decompress($str, $way = 'gzuncompress')
    {
        $str = base64_decode($str);
        $func_compress = array('gzuncompress', 'gzdecode', 'gzinflate', 'bzdecompress');

        //转为ISO-8859-1
        $fileType = mb_detect_encoding($str , array('UTF-8','GBK','LATIN1','BIG5'));
        if($fileType != 'ISO-8859-1'){
            $str = mb_convert_encoding($str ,'ISO-8859-1' , $fileType);
        }
        $mstr = '';
        if (in_array($way, $func_compress)) {
            switch($way){
                case 'gzuncompress':
                    $mstr = gzuncompress($str); // 压缩方法：gzcompress
                    break;

                case 'gzdecode':
                    $mstr = gzdecode($str); // 压缩方法：gzencode php>=5.4
                    break;

                case 'gzinflate':
                    $mstr = gzinflate($str); // 压缩方法：gzdeflate
                    break;

                case 'bzdecompress':
                    $mstr = bzdecompress($str); // 压缩方法：bzcompress
                    break;

                default:
                    return false;
                    break;
            }
        } else {
            return false;
        }

        return $mstr;
    }

}