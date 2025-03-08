<?php

namespace app\api\model\user;

use app\common\model\user\Images as ImagesModel;

/**
 * 用户照片模型
 * Class PointsLog
 * @package app\api\model\user
 */
class Images extends ImagesModel
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
            ->where('is_delete', '=', 0)
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
     * 软删除
     * @return false|int
     */
    public function setDelete()
    {
        return $this->save(['is_delete' => 1]);
    }

}