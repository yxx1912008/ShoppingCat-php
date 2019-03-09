<?php
namespace app\cat\model;

use think\Model;

/**
 * 默认返回数据
 */
class ResponseObj extends Model
{

    /**
     * 处理状态 1.成功 0.失败
     */
    public $status;
    /**
     * 前台显示的提示信息
     */
    public $showMessage;
    /**
     * 返回app端的的json数据字符串
     */
    public $data;

}
