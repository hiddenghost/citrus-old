<?php

namespace app\store\controller\user;
use app\store\controller\Controller;
use app\store\model\Keyword as KeywordModel;

/**
 * 标签管理
 * Class Keyword
 * @package app\store\controller\store
 */
class Keyword extends Controller
{
    /**
     * 门店列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index($keyword = '')
    {
        $model = new KeywordModel;
        $list = $model->getList($keyword);
        return $this->fetch('index', compact('list'));
    }

    /**
     * 添加门店
     * @return array|bool|mixed
     * @throws \Exception
     */
    public function add()
    {
        $model = new KeywordModel;
        if (!$this->request->isAjax()) {

            return $this->fetch('add');
        }
        // 新增记录
        $data = $this->postData('keyword');
        $detail = KeywordModel::detail(['name' => $data['name'], 'is_delete' => 0]);
        if($detail) {
            return $this->renderError('不能重复添加相同的关键词');
        }
        if ($model->add($data)) {
            return $this->renderSuccess('添加成功', url('user.keyword/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 编辑门店
     * @param $keyword_id
     * @return array|bool|mixed
     * @throws \think\exception\DbException
     */
    public function edit($keyword_id)
    {
        // 门店详情
        $model = KeywordModel::detail($keyword_id);
        if (!$this->request->isAjax()) {

            return $this->fetch('edit', compact('model'));
        }
        // 新增记录
        if ($model->edit($this->postData('keyword'))) {
            return $this->renderSuccess('更新成功', url('user.keyword/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 删除门店
     * @param $keyword_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function delete($keyword_id)
    {
        // 门店详情
        $model = KeywordModel::detail($keyword_id);
        if (!$model->setDelete()) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

}