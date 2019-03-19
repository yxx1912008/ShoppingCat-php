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
        Log::error($recText);
        if (strpos($recText, '优惠券') !== false) { //查询字符串中是否包含
            $content = str_replace('优惠券', '', $recText);
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
     * 搜索优惠券商品
     */
    public function searchGood($keyWord)
    {
        $catUrl = Config('CAT_URL') . 'r=index%2Fsearch&s_type=1&kw=' . $keyWords;
        $res = requestUrl($catUrl, 'GET');
        $pattern = '/dtk_data=(.*?);/'; //正则匹配规则
        if (!empty($res) && preg_match($pattern, $res, $result)) {
            return $result[1];
        }
        return null;
    }

}
