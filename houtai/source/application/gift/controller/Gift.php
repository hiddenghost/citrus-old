<?php
/**
 * User: Administrator
 * Date: 19-10-12
 * Time: 下午5:15
 * Created by  zhangheng zhcode@aliyun.com
 */
namespace app\gift\controller;

use think\Config;
use think\Controller;
use think\Cookie;
use think\Db;

class Gift extends Controller{

    public function index(){
        $map['name'] = 'banner';

        $detail = Db::name('gift_config')->where($map)->find();
        if($detail){
            $detail['banner_pic'] = get_img_pic($detail['value']);
        }
        $this->assign('detail',$detail);
        return view();
    }

    public function address(){
        return $this->fetch();
    }

    /**
     * 添加收货地址
     * Created by  zhangheng zhcode@aliyun.com
     */
    public function addresadd(){
        $name = input('name','');
        $address = input('address','');
        $phone = input('phone','');
        if(!$name){
            $this->error('请填写姓名');
        }
        if(!$address){
            $this->error('请填写收货地址');
        }

    }

    public function Receive(){
        $address = Cookie::get('address');

        $order_no = input('order_no','');
        $write_off_num = input('write_off_num');
        if(empty($order_no)){
            $this->error('请填写序列号');
        }
        if(empty($write_off_num)){
            $this->error('请填写验证码');
        }
        $map['order_no'] = $order_no;
        $detail = Db::name('gift_num')->where($map)->find();
        if(!$detail){
            $this->error('序列号不存在');
        }
        $detail['gift'] = Db::name('gift')->where(['id'=>$detail['gift_id']])->find();
        if($detail['gift']['end_time']<time()){
            $this->error('兑换时间已截止');
        }
        $detail['gift']['content'] = htmlspecialchars_decode($detail['gift']['content']);
        $detail['gift']['pic'] = get_img_pic($detail['gift']['image_id']);
        if($address){
            $address = json_decode($address);
        }
        $detail['address'] = $address;

        $statusArr = Config::get('statusarr');
        $detail['statusInfo'] = $statusArr[$detail['status']];
        $this->success('获取成功',url('detail'),$detail);
    }

    /**
     * Created by  zhangheng zhcode@aliyun.com
     */
    public function receiveDo(){
        $reqData = input('post.');
        if(empty($reqData['name'])){
            $this->error('请填写收件人姓名');
        }
        if(empty($reqData['phone'])){
            $this->error('请填写手机号');
        }
        if(empty($reqData['address'])){
            $this->error('请填写详细地址');
        }
        if(!check_mobile($reqData['phone'])){
            $this->error('手机号非法');
        }
        $reqData['address'] = $reqData['Addr'].$reqData['address'];
        //手机号存在则更新
        $map['phone'] = $reqData['phone'];
        $address = Db::name('gift_address')->where($map)->find();

        //更新券的状态为已领取
        $sData['status'] = 1;
        $sData['phone'] = $reqData['phone'];
        Db::name('gift_num')->where(['order_no'=>$reqData['order_no'],'status'=>0])->update($sData);
        if($address){
            Db::name('gift_address')->strict(false)->where($map)->update($reqData);
        }else{
            Db::name('gift_address')->strict(false)->insertGetId($reqData);
        }
        Cookie::forever('address',json_encode($reqData));
        $this->success('领取成功');
    }
    public function detail(){
        exit('detail');
    }
}