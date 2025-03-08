<?php

namespace app\common\model;

/**
 * 拼团拼单成员模型
 * Class GameActiveUsers
 * @package app\common\model\sharing
 */
class GameActiveUsers extends BaseModel
{
    protected $name = 'game_active_users';
    protected $updateTime = false;

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
     * 关联拼团订单表
     * @return \think\model\relation\BelongsTo
     */
    public function gameOrder()
    {
        return $this->belongsTo('GameOrder', 'order_id');
    }

    /**
     * 新增拼团拼单成员记录
     * @param $data
     * @return false|int
     */
    public static function add($data)
    {
        return (new static)->save($data);
    }

}
