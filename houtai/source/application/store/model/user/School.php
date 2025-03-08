<?php

namespace app\store\model\user;

use app\common\model\user\School as SchoolModel;

/**
 * 用户会员等级模型
 * Class Grade
 * @package app\store\model\user
 */
class School extends SchoolModel
{
    /**
     * 获取列表记录
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList()
    {
        return $this->with(['user'])
            ->where('is_delete', '=', 0)
            ->order(['create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => request()->request()
            ]);
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