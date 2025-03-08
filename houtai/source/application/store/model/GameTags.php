<?php

namespace app\store\model;

use app\common\model\GameTags as GameTagsModel;

/**
 * 局标签模型
 * Class GameTags
 * @package app\store\model
 */
class GameTags extends GameTagsModel
{
    /**
     * 获取局标签列表
     * @param $game_id
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($game_id)
    {
        return $this->where('game_id', '=', $game_id)
            ->order(['create_time' => 'asc'])
            ->paginate(15, false, [
                'query' => request()->request()
            ]);
    }

}
