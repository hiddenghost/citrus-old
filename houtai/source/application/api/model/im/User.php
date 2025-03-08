<?php

namespace app\api\model\im;

use app\common\model\im\User as UserModel;

/**
 * 分销商用户模型
 * Class User
 * @package app\api\model\dealer
 */
class User extends UserModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'update_time',
    ];

    /**
     * 新增记录
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function add($data)
    {
        $data['wxapp_id'] = isset(self::$wxapp_id) && self::$wxapp_id ? self::$wxapp_id : 10001;
        return $this->allowField(true)->save($data);
    }

    /**
     * 获取优惠券列表
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($filter)
    {
        if(isset($filter['nameList'])) {
            $this->where('imuser.name', 'IN', $filter['nameList']);
        }
        if(isset($filter['userList'])) {
            $this->where('imuser.user_id', 'IN', $filter['userList']);
        }
        if(isset($filter['keyword']) && $filter['keyword']) {
            $this->where('user.nickName|user.name', 'like', '%' . $filter['keyword'] . '%');
        }
        return $this->alias('imuser')
            ->join('user', 'imuser.user_id = user.user_id')
            ->where('user.is_delete', '=', 0)
            ->where('imuser.is_delete', '=', 0)
            ->order(['imuser.create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => request()->request()
            ]);
    }

    /**
     * 获取所有
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getAll($filter)
    {
        if(isset($filter['nameList'])) {
            $this->where('imuser.name', 'IN', $filter['nameList']);
        }
        if(isset($filter['userList'])) {
            $this->where('imuser.user_id', 'IN', $filter['userList']);
        }
        if(isset($filter['keyword']) && $filter['keyword']) {
            $this->where('user.nickName|user.name', 'like', '%' . $filter['keyword'] . '%');
        }

        return $this->alias('imuser')
            ->join('user', 'imuser.user_id = user.user_id')
            ->where('user.is_delete', '=', 0)
            ->where('imuser.is_delete', '=', 0)
            ->order(['imuser.create_time' => 'desc'])
            ->select()->toArray();
    }

}