<?php

namespace app\store\model\push;

use app\common\model\push\Task as TaskModel;
use app\common\service\Push as Push;

/**
 * 标签模型
 * Class Task
 * @package app\store\model\store
 */
class Task extends TaskModel
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
        return $this->where('is_delete', '=', '0')
            ->order(['create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
    }

    /**
     * 新增推送任务
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function addData($data)
    {
        if (!$this->validateForm($data)) {
            return false;
        }
        $data['wxapp_id'] = self::$wxapp_id;
        if($data['type'] == 1 && $data['user_id']) {
            $usernames = [];
            foreach($data['user_id'] as $user_id) {
                $usernames[] = $user_id;
            }
            $data['usernames'] = implode(',', $usernames);
        }

        return $this->allowField(true)->save($data);
    }

    /**
     * 编辑记录
     * @param $data
     * @return false|int
     */
    public function editData($data)
    {
        if (!$this->validateForm($data)) {
            return false;
        }
        return $this->allowField(true)->save($data) !== false;
    }

    /**
     * 表单验证
     * @param $data
     * @return bool
     */
    private function validateForm($data)
    {
        if (!isset($data['title']) || empty($data['title'])) {
            $this->error = '请输入推送标题';
            return false;
        }
        if (!isset($data['content']) || empty($data['content'])) {
            $this->error = '请输入推送内容';
            return false;
        }
        return true;
    }

}