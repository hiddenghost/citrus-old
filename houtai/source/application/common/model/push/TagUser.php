<?php

namespace app\common\model\push;

use app\common\model\BaseModel;

/**
 * im群组模型
 * Class TagUser
 * @package app\common\model
 */
class TagUser extends BaseModel
{
    protected $name = 'push_tag_user';

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
            $filter['push_tag_user_id'] = (int)$where;
        }

        return static::get($filter, ['user', 'tag']);
    }

    /**
     * 新增记录
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function add($data)
    {
        foreach($data as $key => &$item) {
            $item['wxapp_id'] = isset(self::$wxapp_id) && self::$wxapp_id > 0 ? self::$wxapp_id : 10001;
        }

        return $this->allowField(true)->saveAll($data);
    }

}
