<?php

namespace app\common\model\push;

use app\common\model\BaseModel;

/**
 * im群组模型
 * Class Tag
 * @package app\common\model
 */
class Tag extends BaseModel
{
    protected $name = 'push_tag';

    /**
     * 关联用户表
     * @return \think\model\relation\HasMany
     */
    public function joinuser()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->hasMany("app\\{$module}\\model\\push\\TagUser")->where('is_delete', 0)->order(['push_tag_user_id' => 'asc']);
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
            $filter['push_tag_id'] = (int)$where;
        }

        return static::get($filter, ['joinuser' => ['user']]);
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
