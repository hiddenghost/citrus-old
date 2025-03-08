<?php

namespace app\api\model\blog;

use app\common\model\blog\Comment as CommentModel;
use app\common\model\blog\Blog as BlogModel;

/**
 * Comment模型
 * Class Comment
 * @package app\api\model\user
 */
class Comment extends CommentModel
{
    /**
     * 获取列表
     * @param $userId
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($blog_id, $limit = 10)
    {
        // 获取列表数据
        return $this->with(['category', 'commentList'])
            ->where('blog_id', '=', $blog_id)
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
        $res = $this->allowField(true)->save($data);
        if($res) {
            $blog = BlogModel::detail($data['blog_id']);
            $blog->save(['total_comment' => $blog['total_comment'] + 1]);

            return true;
        }else{
            return false;
        }
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