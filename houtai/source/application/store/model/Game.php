<?php

namespace app\store\model;

use app\common\model\Game as GameModel;

/**
 * 局模型
 * Class Game
 * @package app\store\model
 */
class Game extends GameModel
{

    /**
     * 修改状态
     * @param $state
     * @return false|int
     */
    public function setStatus($state)
    {
        return $this->allowField(true)->save(['status' => $state ? 10 : 20]) !== false;
    }

    /**
     * 软删除
     * @return false|int
     */
    public function setDelete()
    {
        return $this->allowField(true)->save(['is_delete' => 1]);
    }

    /**
     * 获取当前总数
     * @param array $where
     * @return int|string
     * @throws \think\Exception
     */
    public function getGameTotal($where = [])
    {
        return $this->where('is_delete', '=', 0)->where($where)->count();
    }

}
