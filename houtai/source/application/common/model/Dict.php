<?php

namespace app\common\model;

/**
 * 字典模型
 * Class Dict
 * @package app\common\model
 */
class Dict extends BaseModel
{
    protected $name = 'dict';

    /**
     * 字典数据列表
     * @return \think\model\relation\HasMany
     */
    public function datas()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->hasMany("app\\{$module}\\model\\DictData")->order(['sort' => 'asc']);
    }

    /**
     * 详情
     * @param $article_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($dict_id)
    {
        return self::get($dict_id, ['datas']);
    }

}
