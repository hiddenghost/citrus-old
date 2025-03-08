<?php

namespace app\common\model;

/**
 * 局成员模型
 * Class GameUsers
 * @package app\common\model
 */
class GameUsers extends BaseModel
{
    protected $name = 'game_users';

    /**
     * 关联用户表
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->belongsTo("app\\{$module}\\model\\User");
    }

    /**
     * 关联成员订单表
     * @return \think\model\relation\BelongsTo
     */
    public function gameOrder()
    {
        return $this->belongsTo('GameOrder', 'order_id');
    }

    /**
     * 获取tag信息详情
     * @param $gameId
     * @param $userId
     * @return GameUsers|null
     * @throws \think\exception\DbException
     */
    public static function detail($gameId, $userId)
    {
        return static::get(['game_id' => $gameId, 'user_id' => $userId, 'is_delete' => 0]);
    }

}
