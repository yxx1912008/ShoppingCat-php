<?php

namespace app\cat\model;

use think\Model;

class WxAppStatus extends Model
{

    //设置模型对应的数据库表名
    //protected $table = 'cat_wx_app_status';

    //设置主键的字段名称
    protected $pk = 'version_id';

    // 设置当前模型的数据库连接 名称 需要在 database.php中添加
    //protected $connection = 'db_config';

}
