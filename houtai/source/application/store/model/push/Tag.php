<?php

namespace app\store\model\push;

use app\common\model\push\Tag as TagModel;
use app\common\service\Push as Push;

/**
 * 标签模型
 * Class Tag
 * @package app\store\model\store
 */
class Tag extends TagModel
{
    /**
     * 获取列表数据
     * @param null $status
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($status = null)
    {
        !is_null($status) && $this->where('status', '=', (int)$status);
        return $this->with(['joinuser' => ['user']])
            ->where('is_delete', '=', '0')
            ->order(['create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
    }

    /**
     * 获取所有列表
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getAllList()
    {
        return (new self)->where('is_delete', '=', '0')
            ->order(['create_time' => 'desc'])
            ->select();
    }

    /**
     * 新增记录
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function addData($data)
    {
        if (!$this->validateForm($data)) {
            return false;
        }
        $push = new Push;
        $res = $push->addPushLabel($data['name'], $data['description']);
        if($res['rs']) {
            if(count($data['user_id']) > 0) {
                $userList = [];
                foreach($data['user_id'] as $user_id) {
                    $userList[] = [
                        'user_id' => $user_id
                    ];
                    $push->addPushLabelUser($data['name'], $userList);
                }
            }

            return $res['rs'];
        }
        $this->error = $res['message'];
        return false;
    }

    /**
     * 删除标签
     * @param $name
     * @return false|int|void
     */
    public function deleteTag($name)
    {
        $res = (new Push)->deletePushLabel($name);
        if($res['rs']) {
            return $res['rs'];
        }
        $this->error = $res['message'];
        return false;
    }

    /**
     * 表单验证
     * @param $data
     * @return bool
     */
    private function validateForm($data)
    {
        if (!isset($data['name']) || empty($data['name'])) {
            $this->error = '请输入标签名称';
            return false;
        }
        if (!isset($data['description']) || empty($data['description'])) {
            $this->error = '请输入标签描述';
            return false;
        }
        return true;
    }

}