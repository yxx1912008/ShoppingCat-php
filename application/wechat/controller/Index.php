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
            Log::error('日志信息:' . $request->get('echostr'));

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
        } else {
//post
        }
    }

    /**
     * 微信效验参数
     */
    public function checkSignature($timestamp, $echostr, $nonce, $signature)
    {
        $token = '8ff953dd97c4405234a04291dee39e0b';
        $result = SHA1::getSHA1($token, $timestamp, $nonce, '');
        if ($result[0] == 0 && strcasecmp($signature, $result[1]) == 0) {
            return true;
        }
        return false;
    }

}
