<?php

namespace app\api\model\blog;

use app\common\model\blog\Great as GreatModel;
use app\common\model\blog\Blog as BlogModel;

/**
 * Great模型
 * Class Great
 * @package app\api\model\user
 */
class Great extends GreatModel
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
        return $this->with(['user'])
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
        $detail = self::detail(['blog_id' => $data['blog_id'], 'user_id' => $data['user_id'], 'is_delete' => 0]);
        $blog = BlogModel::detail($data['blog_id']);
        if($detail) {
            if($detail['status'] > 0) {
                $detail['status'] = 0;
                $detail->save(['status' => 0]);
                $total_great = $blog['total_great'] - 1;
                $blog->save(['total_great' => $total_great]);
            }else{
                $detail['status'] = 1;
                $detail->save(['status' => 1]);
                $total_great = $blog['total_great'] + 1;
                $blog->save(['total_great' => $total_great]);
            }

            return $total_great;
        }else{
            $data['wxapp_id'] = self::$wxapp_id;
            $res = $this->allowField(true)->save($data);
            $blog = BlogModel::detail($data['blog_id']);
            $total_great = $blog['total_great'] + 1;
            $blog->save(['total_great' => $total_great]);

            return $total_great;
        }
    }
}