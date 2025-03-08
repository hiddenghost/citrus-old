<?php

namespace app\common\model\push;

use app\common\model\BaseModel;

/**
 * im群组模型
 * Class Log
 * @package app\common\model
 */
class Log extends BaseModel
{
    protected $name = 'push_log';

    /**
     * 关联标签表
     * @return \think\model\relation\BelongsTo
     */
    public function task()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->belongsTo("app\\{$module}\\model\\push\\Task", 'push_task_id', 'push_task_id');
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
     * 获取信息详情
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
            $filter['push_log_id'] = (int)$where;
        }

        return static::get($filter, ['user', 'task']);
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
