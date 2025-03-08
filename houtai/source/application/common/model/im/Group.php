<?php

namespace app\common\model\im;

use app\common\model\BaseModel;

/**
 * im群组模型
 * Class ImUser
 * @package app\common\model
 */
class Group extends BaseModel
{
    protected $name = 'im_group';

    /**
     * 关联局表
     * @return \think\model\relation\BelongsTo
     */
    public function game()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->belongsTo("app\\{$module}\\model\\Game");
    }

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
     * 关联商品规格表
     * @return \think\model\relation\HasMany
     */
    public function joinuser()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->hasMany("app\\{$module}\\model\\im\\GroupUser")->where('is_delete', 0)->order(['im_group_id' => 'asc']);
    }

    /**
     * 获取Im信息详情
     * @param $where
     * @return GameUsers|null
     * @throws \think\exception\DbException
     */
    public static function detail($where)
    {
        $filter = ['is_delete' => 0];
        if (is_array($where)) {
            $filter = array_merge($filter, $where);
        } else {
            $filter['im_group_id'] = (int)$where;
        }

        return static::get($filter, ['user', 'game', 'joinuser' => ['user']]);
    }

}
