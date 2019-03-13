<?php
namespace app\cat\controller;

use app\cat\model\BannerModel;
use app\cat\model\ResponseObj;
use app\cat\model\WxAppStatus;
use QL\QueryList; //网页抓取依赖
use think\facade\Cache;
use think\facade\Log;

//缓存依赖

//日志依赖

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
     *
     * 注解：获取微信小程序状态值
     * url: /cat/api/getWxAppStatus.do
     * @param versionId
     * @return
     * @author yuanxx @date 2018年9月18日
     */
    public function getWxAppStatus($versionId = '1.0.0')
    {
        $wxAppStatus = WxAppStatus::get($versionId);
        $catApiUrl = Config('CAT_API_URL'); //购物猫api地址
        $result = [
            'status' => $wxAppStatus['status'],
            'versionId' => $wxAppStatus['version_id'],
            'baseUrl' => $catApiUrl,
        ];

        $model = new ResponseObj([
            'status' => 1,
            'showMessage' => '操作成功',
            'data' => $result,
        ]);
        return json($model);
    }

    /**
     * 获取首页海报
     * @return:
     */
    public function getIndexBanner()
    {
        if (Cache::has('BANNER_CACHE')) { //缓存中是否存在
            $result = Cache::get('BANNER_CACHE');
            return json($result);
        }
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
        Cache::set('BANNER_CACHE', $list, Config('BANNER_CACHE_TIME'));
        return json($list);
    }

    /**
     * 获取咚咚抢商品列表信息
     */
    public function getCurrentQiang()
    {

        if (Cache::has('CUTTENT_BUY')) {
            return json(Cache::get('CUTTENT_BUY'));
        }
        $catUrl = Config('CAT_URL') . 'r=index/wap'; //购物猫网站地址
        $html = QueryList::get($catUrl)->getHtml();
        $pattern = '/indexWillBring","data":(.*?),"mta_name"/'; //正则匹配规则
        if (preg_match($pattern, $html, $result)) {
            $json = json_decode($result[1]);
            Cache::set('CUTTENT_BUY', $json->config->list, 10);
            return json($json->config->list);
        }
        return ['status' => 0, 'messange' => '操作失败', 'data' => ''];

    }

    /**
     *获取正在抢购商品列表
     */
    public function getTicketLive($page = 1)
    {

        $catUrl = Config('CAT_URL') . 'r=index/ajaxnew&page=' . $page; //购物猫网站地址
        if (Cache::has('LIVE_CAC_ID') && !empty(Cache::get('LIVE_CAC_ID'))) {
            $catUrl = $catUrl . '&cac_id=' . Cache::get('LIVE_CAC_ID');
            Log::info('缓存不为空' . $catUrl);
        }
        $res = requestUrl($catUrl, 'GET');
        if (empty($res)) {
            return json(['status' => '0', 'messange' => '操作失败', 'data' => '']);
        }
        if ($page == 1) {
            $json = json_decode($res);
            Cache::set('LIVE_CAC_ID', $json->data->cac_id, 30);
        }
        return $res;
    }

    /**
     * 搜索商品
     */
    public function searchGood($keyWords = '洗护沐浴')
    {
        $catUrl = Config('CAT_URL') . 'r=index%2Fsearch&s_type=1&kw=' . $keyWords;
        $res = requestUrl($catUrl, 'GET');
        $pattern = '/dtk_data=(.*?);/'; //正则匹配规则
        if (!empty($res) && preg_match($pattern, $res, $result)) {
            return $result[1];
        }
        return json(['status' => 0, 'messange' => '操作失败', 'data' => '']);
    }

    /**
     * 获取商品详情
     * 并缓存商品主图列表
     *
     */
    public function getGoodDetail($goodId = '')
    {
        if (empty($goodId)) {
            return json(['status' => 0, 'messange' => '操作失败', 'data' => '']);
        }
        $catUrl = Config('CAT_URL') . 'r=p/d&id=' . $goodId; //请求商品详情的地址
        $res = requestUrl($catUrl, 'GET');
        if (empty($res)) {
            return json(['status' => 0, 'messange' => '操作失败', 'data' => '']);
        }
        $pattern = '/goodsItem = (.*?);/'; //正则匹配规则
        if (!empty($res) && preg_match($pattern, $res, $result)) {
            $json = json_decode($result[1]);
            $rules = [
                'shopName' => ['.info.col-mar > .text > h3', 'text'], //采集首页海报图片地址
                'shopIcon' => ['.info.col-mar > img', 'data-original'],
            ]; //queryList的匹配规则
            $shopInfo = QueryList::rules($rules)->html($res)->query()->getData();
            if (empty($shopInfo)) {
                return json(['status' => 0, 'messange' => '操作失败', 'data' => '']);
            }
            $json->shopIcon = $shopInfo[0]['shopIcon'];
            $json->shopName = $shopInfo[0]['shopName'];
            $imgList = QueryList::html($res)->find('.imglist > img')->attrs('data-original')->map(function ($item) {
                return 'https:' . $item;
            })->all();

            if (!Cache::has('imgList' . $json->goodsid)) {
                //根据商品的真实ID进行商品内容图的存储
                Cache::set('imgList' . $json->goodsid, $imgList, 60 * 60 * 2); //缓存两个小时
            }

            return json($json);
        }
        return json(['status' => 0, 'messange' => '操作失败', 'data' => '']);
    }

    /**
     *
     * 获取商品淘口令
     */
    public function getGoodCodeText($goodId = '')
    {
        if (empty($goodId)) {
            return json(['status' => 0, 'messange' => '操作失败', 'data' => '']);
        }
        $catUrl = Config('CAT_URL') . 'r=p/d&id=' . $goodId; //请求商品详情的地址
        //TODO 关键 获取淘口令需要调用淘宝官方接口
        $html = QueryList::get($catUrl)->getHtml();
        return $html;
    }

    /**
     * 获取商品主图信息
     */
    public function getGoodDescImg($realGoodId = '')
    {
        if (empty($realGoodId) || !Cache::has('imgList' . $realGoodId)) {
            return json(['status' => 0, 'messange' => '操作失败', 'data' => '']);
        }
        return json(Cache::get('imgList' . $realGoodId));
    }

    /**
     * 根据商品真实（即淘宝内部ID）获取商品信息
     */
    public function getGoodDetailByRealId($realGoodId = '')
    {
        if (empty($realGoodId) || !Cache::has('imgList' . $realGoodId)) {
            return json(['status' => 0, 'messange' => '操作失败', 'data' => '']);
        }

        $catUrl = Config('CAT_URL') . 'r=p/d&id=' . $realGoodId . '&type=3'; //请求商品详情的地址
        $res = requestUrl($catUrl, 'GET');
        if (empty($res)) {
            return json(['status' => 0, 'messange' => '操作失败', 'data' => '']);
        }
        $pattern = '/goodsItem = (.*?);/'; //正则匹配规则
        if (!empty($res) && preg_match($pattern, $res, $result)) {
            $json = json_decode($result[1]);
            $rules = [
                'shopName' => ['.info.col-mar > .text > h3', 'text'], //采集首页海报图片地址
                'shopIcon' => ['.info.col-mar > img', 'data-original'],
            ]; //queryList的匹配规则
            $shopInfo = QueryList::rules($rules)->html($res)->query()->getData();
            if (empty($shopInfo)) {
                return json(['status' => 0, 'messange' => '操作失败', 'data' => '']);
            }
            $json->shopIcon = $shopInfo[0]['shopIcon'];
            $json->shopName = $shopInfo[0]['shopName'];
            $imgList = QueryList::html($res)->find('.imglist > img')->attrs('data-original')->map(function ($item) {
                return 'https:' . $item;
            })->all();

            if (!Cache::has('imgList' . $realGoodId)) {
                Cache::set('imgList' . $realGoodId, $imgList, 60 * 60 * 2); //缓存两个小时
            }

            //根据商品的真实ID进行商品内容图的存储
            return json($json);
        }
        return json(['status' => 0, 'messange' => '操作失败', 'data' => '']);
    }

}
