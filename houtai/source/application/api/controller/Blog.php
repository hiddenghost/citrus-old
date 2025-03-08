<?php

namespace app\api\controller;

use app\api\model\blog\Blog as BlogModel;
use app\api\model\User as UserModel;
use app\api\model\blog\Comment as CommentModel;
use app\api\model\blog\Great as GreatModel;
use app\common\model\DictData as DictDataModel;

/**
 * Blog
 * Class Blog
 * @package app\api\controller
 */
class Blog extends Controller
{
    private $user;

    /**
     * 构造方法
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function _initialize()
    {
        parent::_initialize();
        // 验证用户
        $this->user = $this->getUser(true);
    }

    /**
     * 发布的初始化数据
     */
    public function init()
    {
        $topicList = DictDataModel::getListByDict('topic');

        return $this->renderSuccess(compact('topicList'));
    }

    /**
     * 局列表
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function lists($user_id)
    {
        $model = new BlogModel;
        $list = $model->getList($user_id);
        $list->each(function($item, $key) {
            if($item['image_id']) {
                $item['image_id'] = explode(',', $item['image_id']);
            } else{
                $item['image_id'] = [];
            }
            $item['is_great'] = false;
            $great = GreatModel::detail(['blog_id' => $item['blog_id'], 'user_id' => $this->user['user_id'], 'status' => 1, 'is_delete' => 0]);
            if($great) {
                $item['is_great'] = true;
            }

            return $item;
        });

        return $this->renderSuccess(compact('list'));
    }

    /**
     * 发布动态
     */
    public function apply()
    {
        $param = $this->request->param();
        if(!$param['name']) {
            return $this->renderError('标题不能为空');
        }
        if(!$param['image_id']) {
            return $this->renderError('至少上传一张图片');
        }
        $param['user_id'] = $this->user['user_id'];
        $res = (new BlogModel())->add($param);

        return $res ? $this->renderSuccess() : $this->renderError('发布失败');
    }

    /**
     * 点赞/取消点赞某个动态
     * @param $blog_id
     */
    public function agreat($blog_id)
    {
        if(!$blog_id) {
            return $this->renderError('动态不存在');
        }
        $total_great = (new GreatModel)->add(['blog_id' => $blog_id, 'user_id' => $this->user['user_id']]);

        return $this->renderSuccess(['total_great' => $total_great]);

    }

    /**
     * 获取局详情
     * @param $game_id
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function detail($blog_id)
    {
        $detail = BlogModel::detail($blog_id);
        $detail['is_great'] = false;
        $great = GreatModel::detail(['blog_id' => $blog_id, 'user_id' => $this->user['user_id'], 'status' => 1, 'is_delete' => 0]);
        if($great) {
            $detail['is_great'] = true;
        }
        if($detail['image_id']) {
            $detail['image_id'] = explode(',', $detail['image_id']);
        }
        foreach($detail['comment_list'] as $key => &$item) {
            $item['delete_status'] = 0;
            if($item['user_id'] == $this->user['user_id']) {
                $item['delete_status'] = 1;
            }
        }

        return $this->renderSuccess(['detail' => $detail]);
    }

    /**
     * 动态评论
     */
    public function comment()
    {
        $param = $this->request->param();
        if(!$param['blog_id']) {
            return $this->renderError('动态不存在');
        }
        if(!$param['content']) {
            return $this->renderError('评论内容不能为空');
        }
        $param['user_id'] = $this->user['user_id'];
        $res = (new CommentModel())->add($param);

        return $res ? $this->renderSuccess() : $this->renderError('评论失败');
    }

    /**
     * 删除
     * @param $blog_id
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function delete($blog_id)
    {
        $model = BlogModel::detail($blog_id);
        if($model['user_id'] != $this->user['user_id']) {
            return $this->renderError('权限不足');
        }
        if ((new BlogModel())->save(['is_delete' => 1], ['blog_id' => $blog_id])) {
            return $this->renderSuccess([], '删除成功');
        }
        return $this->renderError('删除失败');
    }

    /**
     * 删除评论
     * @param $blog_comment_id
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function deletecomment($blog_comment_id)
    {
        $model = CommentModel::detail($blog_comment_id);
        if($model['user_id'] != $this->user['user_id']) {
            return $this->renderError('权限不足');
        }
        if ((new CommentModel())->save(['is_delete' => 1], ['blog_comment_id' => $blog_comment_id])) {
            $blogModel = BlogModel::detail($model['blog_id']);
            (new BlogModel)->save(['total_comment' => $blogModel['total_comment'] - 1], ['blog_id' => $model['blog_id']]);

            return $this->renderSuccess([], '删除成功');
        }
        return $this->renderError('删除失败');
    }

}