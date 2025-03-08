<?php

namespace app\store\model;

use app\common\model\Dict as DictModel;

/**
 * 字典模型
 * Class Dict
 * @package app\store\model
 */
class Dict extends DictModel
{
    /**
     * 获取文章列表
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList()
    {
        return $this->with(['datas'])
            ->where('is_delete', '=', 0)
            ->paginate(15, false, [
                'query' => request()->request()
            ]);

    }

    /**
     * 获取所有列表
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getAllList()
    {
        return $this->with(['datas'])
            ->where('is_delete', '=', 0)
            ->select();
    }

    /**
     * 新增记录
     * @param $data
     * @return false|int
     */
    public function add($data)
    {
        $data['wxapp_id'] = self::$wxapp_id;
        return $this->allowField(true)->save($data);
    }

    /**
     * 更新记录
     * @param $data
     * @return bool|int
     */
    public function edit($data)
    {
        return $this->allowField(true)->save($data) !== false;
    }

    /**
     * 软删除
     * @return false|int
     */
    public function setDelete()
    {
        return $this->save(['is_delete' => 1]);
    }

}