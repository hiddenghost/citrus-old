<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 19-10-11
 * Time: 上午9:43
 */

namespace app\store\controller\market;

use app\store\controller\Controller;
use app\store\model\Gift as GiftModel;
use app\store\model\GiftNum;
use app\store\service\order\Export as Exportservice;
use think\Config;
use think\Db;
use think\Session;

class Gift extends Controller{
    private $model;
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new GiftModel() ;
        $linkurl ="http://".$_SERVER['HTTP_HOST'].'/'.url('gift/gift/index');
        $bannerConfig = $this->getGiftconfig('banner');
        $this->assign('bannerConfig',$bannerConfig);
        $this->assign('linkurl',$linkurl);
    }

    /**
     * Created by  zhangheng zhcode@aliyun.com
     */
    public function index(){
        $list = $this->model->getList();
        return $this->fetch('index', compact('list'));
    }

    /**
     * 添加优惠券
     * @return array|mixed
     */
    public function add()
    {
        if (!$this->request->isAjax()) {
            return $this->fetch('add');
        }
        $addData = $this->postData('gift');
        $map['number_prefix'] = $addData['number_prefix'];
        $detail = $this->model->where($map)->find();
        if($detail){
            return $this->renderError( '序列号前缀已存在,请更换一个');
        }
        $id = $this->model->add($addData);

        // 新增记录
        if ($id) {
            //添加成功，生成序列号
            $this->addNum($addData,$id);
            return $this->renderSuccess('添加成功', url('market.gift/index'));
        }
        return $this->renderError($this->model->getError() ?: '添加失败');
    }

    /**
     * 生成序列号
     * @param $addData
     * Created by  zhangheng zhcode@aliyun.com
     */
    public function addNum($addData,$id){
        $lenNum = mb_strlen($addData['total_num']);
        for($i=1;$i<=$addData['total_num'];$i++){
            $order_no = sprintf("%0".$lenNum."d",$i);
            $order_no = $addData['number_prefix'].date("Ymd").$order_no;
            $saveData['order_no'] = $order_no;
            $saveData['write_off_num'] = rand_str(6,0,1);
            $saveData['gift_id'] = $id;

            $session = Session::get('yoshop_store');
            $saveData['wxapp_id'] = $session['wxapp']['wxapp_id'];
            $resId = Db::name('gift_num')->insertGetId($saveData);
            unset($saveData);
            unset($order_no);
            usleep(1);
        }
    }

    /**
     * 更新优惠券
     * @param $coupon_id
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function edit($id)
    {
        // 优惠券详情
        $model = GiftModel::detail($id);
        if (!$this->request->isAjax()) {

            $pic = get_img_pic($model->image_id);
            $this->assign('pic',$pic);
            return $this->fetch('edit', compact('model'));
        }
        // 更新记录
        if ($model->edit($this->postData('gift'))) {
            return $this->renderSuccess('更新成功', url('market.gift/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 获取序列号列表
     * Created by  zhangheng zhcode@aliyun.com
     *
     */
    public function numlist(){
        $model = new GiftNum();
        $map['gift_id'] = input('id');
        $orderNo = input('order_no','');
        if($orderNo){
            $map['order_no'] = $orderNo;
        }
        $list = $model->getList($map);
        $statusArr = Config::get('statusarr');
        foreach($list as &$v){
            $v['statusInfo'] = $statusArr[$v['status']];

        }
        return $this->fetch('numlist', compact('list'));
    }

    /**
     * 导出序列号
     * Created by  zhangheng zhcode@aliyun.com
     */
    public function export(){
        $id = input('id');
        $detail = GiftModel::detail($id);
        $map['gift_id'] = $id;
        $list = Db::name('gift_num')->where($map)->select();
        return (new Exportservice)->giftNum($list,$detail);
    }

    /**
     * Created by  zhangheng zhcode@aliyun.com
     */
    public function giftconfig(){
        $saveData = input("post.");
        $name = input('name');
        $value = input('value');
        $map['name'] = $name;
        $session = Session::get('yoshop_store');
        $saveData['wxapp_id'] = $session['wxapp']['wxapp_id'];
        $detail = Db::name('gift_config')->where($map)->find();
        if($detail){
            Db::name('gift_config')->where($map)->update($saveData);
        }else{
            Db::name('gift_config')->insertGetId($saveData);
        }
        return $this->renderSuccess('保存成功');
    }

    /**
     * @param string $name
     * @return array|false|\PDOStatement|string|\think\Model
     * Created by  zhangheng zhcode@aliyun.com
     */
    public function getGiftconfig($name='banner'){
        $session = Session::get('yoshop_store');
        $map['wxapp_id'] = $session['wxapp']['wxapp_id'];
        $map['name'] = $name;
        $detail = Db::name('gift_config')->where($map)->find();
        if($name == 'banner'){
            //返回图片地址
            $detail['banner_pic'] = get_img_pic($detail['value']);
        }
        return $detail;
    }

    /**
     * 领取记录
     * Created by  zhangheng zhcode@aliyun.com
     */
    public function receive(){
        $model = new GiftNum();
        $map['gift_id'] = input('id');
        $map['status'] = input('status',1);

        $orderNo = input('order_no','');
        if($orderNo){
            $map['order_no'] = $orderNo;
        }
        $list = $model->getList($map);
        foreach($list as &$v){
            $v['address'] = Db::name('gift_address')->where(['phone'=>$v['phone']])->find();
        }
        $statusArr = Config::get('statusarr');
        foreach($list as &$v){
            $v['statusInfo'] = $statusArr[$v['status']];

        }
        $this->assign('status',$map['status']);
        return $this->fetch('receivelist', compact('list'));
    }

    /**
     * Created by  zhangheng zhcode@aliyun.com
     */
    public function setstatus(){
        $id = input('id');
        $status = input('status',2);
        Db::name('gift_num')->where(['id'=>$id])->update(['status'=>$status]);
        $this->success('操作成功');
    }
} 