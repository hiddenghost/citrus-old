<?php

namespace app\store\model\user;

use app\common\model\user\Images as ImagesModel;

use app\store\model\User as UserModel;

/**
 * 用户照片模型
 * Class Grade
 * @package app\store\model\user
 */
class Images extends ImagesModel
{
    /**
     * 获取列表记录
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($user_id)
    {
        return $this->where('is_delete', '=', 0)
            ->where('user_id', '=', $user_id)
            ->order(['create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => request()->request()
            ]);
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