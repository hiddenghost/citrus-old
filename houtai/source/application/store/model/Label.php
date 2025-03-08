<?php

namespace app\store\model;
use app\common\model\Label as LabelModel;
use think\Model;


/**
 * 标签模型
 * Class Label
 * @package app\store\model
 */
class Label extends LabelModel
{
    /**
     * 获取优惠券列表
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList()
    {
        return $this->where('is_delete', '=', 0)
            ->order(['data_id' => 'asc', 'sort' => 'asc'])
            ->paginate(15, false, [
                'query' => request()->request()
            ]);
    }

    /**
     * 添加新记录
     * @param $data
     * @return false|int
     */
    public function add($data)
    {
        $data['wxapp_id'] = self::$wxapp_id;
        $data['create_time'] = time();
        return $this->allowField(true)->insertGetId($data);
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
     * 删除记录 (软删除)
     * @return bool|int
     */
    public function setDelete()
    {
        return $this->save(['is_delete' => 1]) !== false;
    }

}
