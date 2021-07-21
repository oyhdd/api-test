<?php

namespace App\Models;

/**
 * 告警服务:暂支持企业微信群聊告警
 */
class Alarm
{
    const WEACHAT_URL = 'https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=';

    /**
     * 发送企业微信告警
     * @param string $msg           告警内容
     * @param string $title         告警标题
     * @param array  $robot_keys    企业微信群聊机器人的key
     *
     * @return void
     */
    public static function alarmQyWeachat(string $msg, string $title = '', $robot_keys = [])
    {
        if (empty($robot_keys)) {
            return;
        }

        try {
            foreach ($robot_keys as $robot_key) {
                $client = new \GuzzleHttp\Client();
                $client->request('POST', self::WEACHAT_URL . $robot_key, [
                    'headers' => ['Content-Type' => 'application/json'],
                    'json' => [
                        'msgtype' => 'markdown',
                        'markdown' => [
                            'content' => sprintf("【API-TEST】%s\n>%s\n", $title, self::cut_substr($msg)),
                            'mentioned_list' => ['@all'],
                        ]
                    ],
                    'timeout' => 1,
                ]);
            }
        } catch (\Throwable $th) {
        }
    }

    /**
     * 截取指定长度的字符串，超出长度以省略号(...)填补尾部显示
     * @ str 字符串
     * @ len 指定长度
     **/
    public static function cut_substr($str, int $len = 512)
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