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

Route::group('cat/api', [
    '/getIndexBanner' => ['cat/api/getIndexBanner'], // 获取首页海报
    '/getWxAppStatus' => ['cat/api/getWxAppStatus'], //查询微信小程序状态
    '/getCurrentQiang' => ['cat/api/getCurrentQiang'], //获取正在疯抢商品列表商品列表
    '/getTicketLive' => ['cat/api/getTicketLive'], //获取正在抢购商品列表
    '/searchGood' => ['cat/api/searchGood'], //搜索商品
    '/getGoodDetail' => ['cat/api/getGoodDetail'], //获取商品详情
    '/getGoodCodeText' => ['cat/api/getGoodCodeText'], //获取商品淘口令
    '/getGoodDescImg' => ['cat/api/getGoodDescImg'], //根据商品的真实ID获取商品的主图信息
    '/getGoodDetailByRealId' => ['cat/api/getGoodDetailByRealId'], //根据商品真实（即淘宝内部ID）获取商品信息
    '/queryAgent' => ['cat/api/queryAgent'], //伪装页面，查询代理区域
    '/clearCache' => ['cat/api/clearCache'],
])->ext('do')->method('POST');

return [

];
