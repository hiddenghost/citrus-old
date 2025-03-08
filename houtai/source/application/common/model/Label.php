<?php

namespace app\common\model;

/**
 * 标签模型
 * Class Tags
 * @package app\common\model
 */
class Label extends BaseModel
{
    protected $name = 'label';

    /**
     * 关联分类
     * @return \think\model\relation\HasOne
     */
    public function category()
    {
        return $this->hasOne("DictData", 'data_id', 'data_id');
    }

    /**
     * name
     * @param $value
     * @return mixed
     */
    public function getNameAttr($value)
    {
        return htmlspecialchars_decode($value, ENT_QUOTES);
    }

    /**
     * 获取sku信息详情
     * @param $lable_id
     * @return Tags|null
     * @throws \think\exception\DbException
     */
    public static function detail($lable_id)
    {
        return static::get($lable_id, ['category']);
    }

}
