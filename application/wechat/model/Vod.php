<?php
namespace app\wechat\model;

use think\Model;

class Vod extends Model
{

    protected $pk = 'vod_id';

    // 设置当前模型对应的完整数据表名称
    protected $table = 'mac_vod';

    // 设置当前模型的数据库连接
    protected $connection = 'movie_database';

}
