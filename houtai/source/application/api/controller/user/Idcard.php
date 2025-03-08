<?php

namespace app\api\controller\user;

use app\api\controller\Controller;
use app\api\model\user\Idcard as IdcardModel;

/**
 * 订单售后服务
 * Class service
 * @package app\api\controller\user\order
 */
class Idcard extends Controller
{
    /* @var \app\api\model\User $user */
    private $user;

    /**
     * 构造方法
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function _initialize()
    {
        parent::_initialize();
        $this->user = $this->getUser(true);   // 用户信息
    }

    /**
     * 申请认证
     * @return array
     * @throws \think\exception\DbException
     * @throws \Exception
     */
    public function apply()
    {
        // 订单商品详情
        $idcard = IdcardModel::detail($this->user['user_id']);
        if($idcard && $idcard['status'] < 1) {
            return $this->renderError('身份证已提交，待审核中');
        }
        if($idcard && $idcard['status'] == 1) {
            return $this->renderError('身份证已认证成功');
        }
        $param = $this->request->post();
        if ($idcard) {
            $param['check_time'] = '';
            $param['check_result'] = '';
            $param['status'] = 0;
            if($idcard->edit($param)) {
                return $this->renderSuccess([], '提交成功');
            }
        }else{
            $model = new IdcardModel;
            $param['user_id'] = $this->user['user_id'];
            if($model->add($param)) {
                return $this->renderSuccess([], '提交成功');
            }
        }

        return $this->renderError('提交失败');
    }

    /**
     * 详情
     * @return array
     * @throws \think\exception\DbException
     */
    public function detail()
    {
        // 详情
        $detail = IdcardModel::detail($this->user['user_id']);

        return $this->renderSuccess(compact('detail'));
    }

}