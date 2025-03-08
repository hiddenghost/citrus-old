<?php

namespace app\store\model;

use app\common\model\GameActive as ActiveModel;

/**
 * 拼团拼单模型
 * Class GameActive
 * @package app\store\model\sharing
 */
class GameActive extends ActiveModel
{
    /**
     * 获取拼单列表
     * @param null $active_id
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($active_id = null)
    {
        $active_id > 0 && $this->where('active_id', '=', $active_id);
        return $this->with(['user', 'gameOrder'])
            ->order(['create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => request()->request()
            ]);
    }

}
