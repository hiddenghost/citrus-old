<?php

namespace app\store\model;

use app\common\model\GameActiveUsers as GameActiveUsersModel;

/**
 * 拼团拼单成员模型
 * Class GameActiveUsers
 * @package app\store\model\sharing
 */
class GameActiveUsers extends GameActiveUsersModel
{
    /**
     * 获取拼单成员列表
     * @param $active_id
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($active_id)
    {
        return $this->with(['gameOrder', 'user'])
            ->where('active_id', '=', $active_id)
            ->order(['create_time' => 'asc'])
            ->paginate(15, false, [
                'query' => request()->request()
            ]);
    }

}
