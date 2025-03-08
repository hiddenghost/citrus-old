<?php

namespace app\common\model;
use app\common\model\Dict as DictModel;

/**
 * 字典模型
 * Class DictData
 * @package app\common\model
 */
class DictData extends BaseModel
{
    protected $name = 'dict_data';

    /**
     * 关联字典表
     * @return \think\model\relation\BelongsTo
     */
    public function dict()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->belongsTo("app\\{$module}\\model\\Dict");
    }

    /**
     * 分类图片
     * @return \think\model\relation\HasOne
     */
    public function image()
    {
        return $this->hasOne('uploadFile', 'file_id', 'image_id');
    }

    /**
     * 根据字典获取列表数据
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public static function getListByDict($code)
    {
        $dict = DictModel::detail(['code' => $code]);
        return self::where('is_delete', '=', 0)
            ->where('dict_id', '=', $dict['dict_id'])
            ->select();
    }

    /**
     * 详情
     * @param $dict_data_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($dict_data_id)
    {
        return self::get($dict_data_id, ['dict', 'image']);
    }

}
