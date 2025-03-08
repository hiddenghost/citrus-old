<?php

namespace app\common\model;

/**
 * 局申请模型
 * Class GameApply
 * @package app\common\model
 */
class GameApply extends BaseModel
{
    protected $name = 'game_apply';

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
    public function game()
    {
        return $this->belongsTo('Game');
    }

    /**
     * 获取tag信息详情
     * @param $gameId
     * @param $userId
     * @return GameUsers|null
     * @throws \think\exception\DbException
     */
    public static function detail($where)
    {
        $filter = ['is_delete' => 0];
        if (is_array($where)) {
            $filter = array_merge($filter, $where);
        } else {
            $filter['game_apply_id'] = (int)$where;
        }

        return static::get($filter, ['user', 'game']);
    }

}
