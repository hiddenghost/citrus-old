<?php

namespace app\store\model;
use app\common\model\BaseModel;
use think\Model;


/**
 * 优惠券模型
 * Class Coupon
 * @package app\store\model
 */
class Gift extends BaseModel
{
    /**
     * 获取优惠券列表
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($map='',$order='id asc')
    {
        return $this->where('is_delete', '=', 0)
            ->order($order)
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
        $data['end_time'] = strtotime($data['end_time']);
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
       $data['end_time'] = strtotime($data['end_time']);
       $data['update_time'] = time();
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

    public static function detail($id){
        $detail = self::find($id);
        return $detail;
    }
}
