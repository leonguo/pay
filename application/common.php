<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

function json_ok($data)
{
    $data = array("ok" => true, "data" => $data, "reason" => "");
    return json($data);
}

function json_fail($err,$code = 200)
{
    $data = array("ok" => false, "data" => [], "reason" => $err);
    return json($data,$code);
}

function getMillisecond() {
    list($t1, $t2) = explode(' ', microtime());
    return (float) sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
}

/**
 * 创建一个全局唯一的订单ID
 * 订单ID包含
 */
function createOrderId() {
    //从17年开始到现在的毫秒(39bit) + 机房ID(2bit) + 设备ID(7bit) + 随机数(8bit) + 预留(8bit) = 数字(64bit)
    //取64bit数字的HEX码
    $since = getMillisecond() - strtotime(date('2017-01-01 00:00:00')) * 1000;
    $h = str_pad(dechex(intval($since / pow(2, 7))), 8, 0, STR_PAD_LEFT);
    $l = str_pad(dechex((($since & 0x7F) << 25) + (0 << 23) + (0 << 16) + (mt_rand(0, 0xFF) << 8)), 8, 0, STR_PAD_LEFT);
    $order_id = strtoupper(sprintf('%s%s', $h, $l));
    return $order_id;
}