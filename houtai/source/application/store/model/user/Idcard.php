<?php

namespace app\store\model\user;

use app\common\model\user\Idcard as IdcardModel;

/**
 * 用户会员等级模型
 * Class Grade
 * @package app\store\model\user
 */
class Idcard extends IdcardModel
{
    /**
     * 获取列表记录
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList()
    {
        return $this->with(['user' => ['images']])
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