<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\Route;

Route::get('/index','Index/index');

Route::get('/pay/activityinfo','Pay/activityInfo');

Route::get('/pay/getopeninfo','Pay/getOpenInfo');

//下单接口
Route::post('/pay/genorder','Pay/genOrder');

//异步回调
Route::post('/pay/notify','Pay/notify');
