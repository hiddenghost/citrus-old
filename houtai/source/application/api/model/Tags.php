<?php

namespace app\api\model;

use app\common\model\Tags as TagsModel;

/**
 * 标签模型
 * Class Tags
 * @package app\common\model
 */
class Tags extends TagsModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'wxapp_id',
        'create_time',
        'update_time'
    ];

    /**
     * 获取列表
     * @param int $category_id
     * @param int $limit
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList()
    {
        return $this->with(['image', 'activeimage'])
            ->where('is_delete', '=', 0)
            ->order(['sort' => 'asc', 'create_time' => 'desc'])
            ->select();
    }

}
