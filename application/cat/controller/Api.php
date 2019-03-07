<?php
namespace app\cat\controller;

use QL\QueryList;

/**
 * 购物猫相关api接口
 */
class Api
{
    public function index()
    {
        return 'this is api';
    }

    /**
     * 获取首页海报
     * @return:
     */
    public function getIndexBanner()
    {
        //采集某页面所有的图片
        $data = QueryList::get('http://cms.querylist.cc/bizhi/453.html')->find('img')->attrs('src');
        //打印结果
        print_r($data->all());
    }

}
