<?php
namespace app\wechat\controller;

use app\wechat\model\SHA1;
use think\facade\Log;
use think\Request;

class Index
{
    public function index()
    {
        return 'this is wechat';
    }

    /**
     * 微信授权处理
     */
    public function wxAuthen(Request $request)
    {
        if ($request->method() == 'GET') {
            $echostr = $request->get('echostr');
            $nonce = $request->get('nonce');
            $signature = $request->get('signature');
            $timestamp = $request->get('timestamp');
            if (empty($echostr) || empty($nonce) || empty($signature) || empty($timestamp)) {
                return 'failed';
            }
            if ($this->checkSignature($timestamp, $echostr, $nonce, $signature)) {
                return $echostr;
            }
            return 'failed';
        } else { //post
            $this->handleWechat();
            return 'OK';
        }
    }

    /**
     * 微信效验参数
     */
    public function checkSignature($timestamp, $echostr, $nonce, $signature)
    {
        $token = '8ff953dd97c4405234a04291dee39e0b'; //微信管理后台设置的token
        $result = SHA1::getSHA1($token, $timestamp, $nonce, '');
        if ($result[0] == 0 && strcasecmp($signature, $result[1]) == 0) {
            return true;
        }
        return false;
    }

    /**
     * 处理微信请求
     */
    public function handleWechat()
    {
        // $postArr = $GLOBALS['HTTP_RAW_POST_DATA']; //读取微信传过来的文件流
        $postArr = file_get_contents("php://input");
        Log::error($postArr);
        $postObj = simplexml_load_string($postArr);

        if (strtolower($postObj->MsgType) == 'event') {
            if (strtolower($postObj->Event == 'subscribe')) { //如果是订阅事件
                $this->handleSubscribe($postObj->ToUserName, $postObj->FromUserName);
            }
        }

        if (strtolower($postObj->MsgType) == 'text') { //是否是用户的文本消息
            $this->handleText($postObj);
        }

        return 'OK';
    }

    /**
     * 处理用户发来的微信消息
     */
    private function handleText($postObj)
    {
        //回复用户消息(纯文本格式)
        $toUser = $postObj->FromUserName;
        $fromUser = $postObj->ToUserName;
        $recText = (String) $postObj->Content;
        $content = '';
        if (strpos($recText, '优惠券') !== false) { //查询字符串中是否包含
            $content = str_replace('优惠券', '', $recText);
            $goodInfos = $this->searchGood($content); //搜索到的商品
            $result = [
                'title' => $goodInfos[0]['d_title'],
                'description' => $goodInfos[0]['description'],
                'picUrl' => $goodInfos[0]['pic'],
            ];
            return;
        } else {
            $content = '没找到';
        }
        $this->returText($fromUser, $toUser, $content);
    }

    /**
     * 返回纯文本微信消息
     */
    private function returText($fromUser, $toUser, $content)
    {
        $time = time();
        $msgType = 'text';
        $template = "<xml>
        <ToUserName><![CDATA[%s]]></ToUserName>
        <FromUserName><![CDATA[%s]]></FromUserName>
        <CreateTime>%s</CreateTime>
        <MsgType><![CDATA[%s]]></MsgType>
        <Content><![CDATA[%s]]></Content>
        </xml>";
        $info = sprintf($template, $toUser, $fromUser, $time, $msgType, $content);
        echo $info;
    }

    /**
     * 处理订阅事件
     */
    public function handleSubscribe($fromUser, $toUser)
    {
        $content = '/:heart欢迎关注购物猫 /:rose\r\n/:heart查找优惠券，请在前面加 “优惠券“\r\n/:heart例如：想找耳机优惠券，输入： 优惠券 耳机 \r\n/:heart指定商品查找优惠券，先复制淘宝商品完整标题 \r\n/:heart输入 :优惠券 淘宝标题，然后发给我\r\n/:coffee查找电影，请在电影名前加“电影”\r\n/:coffee要求管理员添加电影 请在电影名前加“添加”';
        $this->returText($fromUser, $toUser, $content);
    }

    /**
     * 搜索优惠券商品
     */
    public function searchGood($keyWord)
    {
        $catUrl = Config('CAT_URL') . 'r=index%2Fsearch&s_type=1&kw=' . $keyWord;
        Log::error('请求网址：'.$catUrl);
        $res = requestUrl($catUrl, 'GET');
        Log::error($res);
        $pattern = '/dtk_data=(.*?);/'; //正则匹配规则
        if (!empty($res) && preg_match($pattern, $res, $result)) {
            return json_decode($result[1], true); //转换为数组
        }
        return;
    }

/**
 * 回复图文消息
 */
    public function returnNews($fromUser, $toUser, $content)
    {
        $time = time();
        $msgType = 'news';
        $template = '<xml>
        <ToUserName><![CDATA[%s]]></ToUserName>
        <FromUserName><![CDATA[%s]]></FromUserName>
        <CreateTime>%s</CreateTime>
        <MsgType><![CDATA[news]]></MsgType>
        <ArticleCount>1</ArticleCount>
        <Articles>
          <item>
            <Title><![CDATA[%s]]></Title>
            <Description><![CDATA[%s]]></Description>
            <PicUrl><![CDATA[%s]]></PicUrl>
            <Url><![CDATA[%s]]></Url>
          </item>
        </Articles>
      </xml>';
        $info = sprintf($template, $toUser, $fromUser, $time, $msgType, $content['title'], $content['description'], $content['picUrl'], '');
        echo $info;
    }

}
