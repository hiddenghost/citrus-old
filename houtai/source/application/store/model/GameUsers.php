<?php

namespace app\store\model;

use app\common\model\GameUsers as GameUsersModel;

/**
 * 局成员模型
 * Class GameUsers
 * @package app\store\model
 */
class GameUsers extends GameUsersModel
{
    /**
     * 获取拼单成员列表
     * @param $game_id
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($game_id)
    {
        return $this->with(['GameOrder', 'user'])
            ->where('game_id', '=', $game_id)
            ->order(['create_time' => 'asc'])
            ->paginate(15, false, [
                'query' => request()->request()
            ]);
    }

}
