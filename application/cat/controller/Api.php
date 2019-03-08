<?php
namespace app\cat\controller;

use app\cat\model\BannerModel;
use QL\QueryList;

/**
 * 购物猫相关api接口
 */
class Api
{

    public function index()
    {

        $pattern = '/id=(\d+)&/';
        if (preg_match($pattern, '/index.php?r=l/d&id=18547878&nav_wrap=l&u=675425')) {
            echo '匹配通过';
        } else {
            echo '匹配no通过';
        }
        return 'this is api';
    }

    /**
     * 获取首页海报
     * @return:
     */
    public function getIndexBanner()
    {
        $catUrl = Config('CAT_URL'); //购物猫网站地址
        $rules = [
            'bannerImg' => ['.banner-center > .swiper-wrapper > .swiper-slide a img', 'src'], //采集首页海报图片地址
            'goodId' => ['.banner-center > .swiper-wrapper > .swiper-slide > a', 'href'],
        ]; //queryList的匹配规则
        $html = QueryList::get($catUrl)->rules($rules)->query()->getData();
        $list = []; //存储返回结果
        $pattern = '/id=(\d+)&/'; //正则匹配规则
        for ($i = 0; $i < count($html); $i++) {
            $str = $html[$i]['goodId']; //商品ID
            $picUrl = $html[$i]['bannerImg']; //图片地址
            if (preg_match($pattern, $str, $result)) {
                $model = new BannerModel([
                    'bannerImg' => $picUrl,
                    'goodId' => $result[1],
                ]);
                array_push($list, $model);
            }
        }
        return json($list);
    }
}