<?php

namespace app\common\model;

/**
 * 标签模型
 * Class Tags
 * @package app\common\model
 */
class Tags extends BaseModel
{
    protected $name = 'tags';

    /**
     * 未选中图片
     * @return \think\model\relation\HasOne
     */
    public function image()
    {
        return $this->hasOne('uploadFile', 'file_id', 'image_id');
    }

    /**
     * 选重图片
     * @return \think\model\relation\HasOne
     */
    public function activeimage()
    {
        return $this->hasOne('uploadFile', 'file_id', 'active_image_id');
    }

    /**
     * 获取sku信息详情
     * @param $tagsId
     * @return Tags|null
     * @throws \think\exception\DbException
     */
    public static function detail($tagsId)
    {
        return static::get($tagsId);
    }

}
