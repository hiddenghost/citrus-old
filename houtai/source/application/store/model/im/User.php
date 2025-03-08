<?php

namespace app\store\model\im;

use app\common\model\im\User as UserModel;

/**
 * Im用户模型
 * Class Grade
 * @package app\store\model\user
 */
class User extends UserModel
{
    /**
     * 获取列表记录
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList()
    {
        return $this->with(['user'])
            ->where('is_delete', '=', 0)
            ->paginate(15, false, [
                'query' => request()->request()
            ]);
    }

    /**
     * 得到所有的用户数
     * @return int|string
     * @throws \think\Exception
     */
    public function getCount()
    {
        return $this->where('is_delete', '=', 0)->count();
    }

}