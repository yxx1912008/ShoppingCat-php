<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

Route::get('think', function () {
    return 'hello,ThinkPHP5!';
});
Route::post('hello/:name', 'index/hello');
// 获取首页海报
Route::post('cat/api/getIndexBanner.do', 'cat/api/getIndexBanner');
//查询微信小程序状态
Route::post('cat/api/getWxAppStatus.do', 'cat/api/getWxAppStatus');
//获取正在疯抢商品列表商品列表
Route::post('cat/api/getCurrentQiang.do', 'cat/api/getCurrentQiang');

return [

];
