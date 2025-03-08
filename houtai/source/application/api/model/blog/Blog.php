<?php

namespace app\api\model\blog;

use app\common\model\blog\Blog as BlogModel;

/**
 * Blog模型
 * Class Blog
 * @package app\api\model\user
 */
class Blog extends BlogModel
{
    /**
     * 获取列表
     * @param $userId
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($userId, $limit = 10)
    {
        // 获取列表数据
        return $this->with(['category', 'commentList'])
            ->where('user_id', '=', $userId)
            ->where('is_delete', '=', 0)
            ->order(['create_time' => 'desc'])
            ->paginate($limit, false, [
                'query' => \request()->request()
            ]);
    }

    /**
     * 新增记录
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function add($data)
    {
        $data['wxapp_id'] = self::$wxapp_id;
        return $this->allowField(true)->save($data);
    }

    /**
     * 编辑记录
     * @param $data
     * @return false|int
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