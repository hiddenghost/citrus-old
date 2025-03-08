<?php

namespace app\common\model;

/**
 * 局收藏模型
 * Class GameCollect
 * @package app\common\model
 */
class GameCollect extends BaseModel
{
    protected $name = 'game_collect';
    protected $updateTime = false;

    /**
     * 关联局表
     * @return \think\model\relation\BelongsTo
     */
    public function game()
    {
        return $this->belongsTo('Game');
    }

    /**
     * 关联局表
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('User');
    }

    /**
     * 详情
     * @param $game_id
     * @param $user_id
     * @return GameCollect|null
     * @throws BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public static function detail($game_id, $user_id)
    {
        return self::get(['game_id' => $game_id, 'user_id' => $user_id, 'is_delete' => 0], ['game', 'user']);
    }

}
