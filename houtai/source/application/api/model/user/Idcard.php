<?php

namespace app\api\model\user;

use app\common\model\user\Idcard as IdcardModel;

/**
 * 用户身份证模型
 * Class PointsLog
 * @package app\api\model\user
 */
class Idcard extends IdcardModel
{
    /**
     * 获取列表
     * @param $userId
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($userId)
    {
        // 获取列表数据
        return $this->where('user_id', '=', $userId)
            ->order(['create_time' => 'desc'])
            ->select();
    }

    /**
     * 新增记录
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function add($data)
    {
        $data['wxapp_id'] = self::$wxapp_id;
        return $this->allowField(true)->save($data);
    }

    /**
     * 编辑记录
     * @param $data
     * @return false|int
     */
    public function edit($data)
    {
        return $this->allowField(true)->save($data) !== false;
    }

    /**
     * 软删除
     * @return false|int
     */
    public function setDelete()
    {
        return $this->save(['is_delete' => 1]);
    }

}