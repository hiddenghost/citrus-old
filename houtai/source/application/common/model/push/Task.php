<?php

namespace app\common\model\push;

use app\common\model\BaseModel;

/**
 * im群组模型
 * Class Task
 * @package app\common\model
 */
class Task extends BaseModel
{
    protected $name = 'push_task';

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
     * 关联标签表
     * @return \think\model\relation\BelongsTo
     */
    public function tag()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->belongsTo("app\\{$module}\\model\\push\\Tag", 'push_tag_id', 'push_tag_id');
    }

    /**
     * 关联商品规格表
     * @return \think\model\relation\HasMany
     */
    public function log()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->hasMany("app\\{$module}\\model\\push\\Log")->where('is_delete', 0)->order(['push_log_id' => 'asc']);
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
            $filter['push_task_id'] = (int)$where;
        }

        return static::get($filter, ['game', 'tag', 'log' => ['user']]);
    }

    /**
     * 新增记录
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function add($data)
    {
        $data['wxapp_id'] = self::$wxapp_id;
        return $this->allowField(true)->save($data);
    }

}
