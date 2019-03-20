<?php
namespace app\cat\controller;

use think\Db;

/**
 * 购物猫相关类
 */
class Index
{
    public function index()
    {

        $res = Config('CAT_URL');
        return $res;
        return 'this is cat module';
    }

    /**
     * 测试数据库
     */
    public function testDb()
    {
        // 数据库查询
        $result = Db::query('select * from cat_wx_app_status where version_id=:id', ['id' => '1.0.0']);
        // 数据库增加
        $insertResult = Db::execute('insert into cat_wx_app_status (version_id, status) values (:id, :status)', ['id' => '1.0.2', 'status' => '1']);
        // 数据库删除
        // $delResult = Db::execute('delete from cat_wx_app_status where version_id=:id', ['id' => '1.0.2']);
        // 数据库变更
        $updateResult = Db::execute('update  cat_wx_app_status set status=:status where version_id=:id', ['status' => 0, 'id' => '1.0.2']);
        // 测试数据库事物
        Db::startTrans();
        try {
            $insertResult = Db::execute('insert into cat_wx_app_status (version_id, status) values (:id, :status)', ['id' => '1.0.2', 'status' => '1']);
            $updateResult = Db::execute('update  cat_wx_app_status set status=:status where version_id=:id', ['status' => 5, 'id' => '1.0.2']);
        } catch (\Throwable $th) {
            Db::rollback();
        }
        return json(['status' => '1', 'messange' => '操作成功', 'data' => '']);
    }

}
