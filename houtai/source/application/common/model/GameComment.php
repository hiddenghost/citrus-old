<?php

namespace app\common\model;

/**
 * 局收藏模型
 * Class GameComment
 * @package app\common\model
 */
class GameComment extends BaseModel
{
    protected $name = 'game_comment';
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
     * @param $game_comment_id
     * @return GameCollect|null
     * @throws BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public static function detail($game_comment_id)
    {
        return self::get($game_comment_id, ['game', 'user']);
    }

}
