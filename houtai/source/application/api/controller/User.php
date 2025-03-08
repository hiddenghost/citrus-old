<?php

namespace app\api\controller;

use app\api\model\User as UserModel;
use app\api\model\user\Email as EmailModel;
use app\api\model\user\Grade as GradeModel;
use app\api\model\Category as CategoryModel;
use app\api\model\UploadFile;
use app\api\model\Label as LabelModel;
use app\api\model\user\Label as UserLabelModel;
use app\api\model\im\Relation as RelationModel;
use app\common\library\sms\Driver as SmsDriver;
use app\common\model\Setting as SettingModel;
use app\api\model\user\Images as ImagesModel;
use app\api\model\GameUsers as GameUsersModel;
use app\api\model\Goods as GoodsModel;
use app\api\model\GoodsSku as GoodsSkuModel;

/**
 * 用户管理
 * Class User
 * @package app\api
 */
class User extends Controller
{
    /**
     * 微信快捷登录
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function login()
    {
        $model = new UserModel;
        return $this->renderSuccess([
            'user_id' => intval($model->login($this->request->post())),
            'token' => $model->getToken()
        ]);
    }

    /**
     * iphone快捷登录
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function iphonelogin()
    {
        $model = new UserModel;
        return $this->renderSuccess([
            'user_id' => intval($model->iphonelogin($this->request->post())),
            'token' => $model->getToken()
        ]);
    }

    /**
     * 当前用户详情
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function detail()
    {
        // 当前用户信息
        $userInfo = $this->getUser(true);
        $userInfo['is_school'] = 0;
        $userInfo['is_idcard'] = 0;
        if(isset($userInfo['school_data']) && isset($userInfo['school_data'][0]) && $userInfo['school_data'][0]['status'] == 1) {
            $userInfo['is_school'] = 1;
        }
        if(isset($userInfo['idcard_data']) && isset($userInfo['idcard_data'][0]) && $userInfo['idcard_data'][0]['status'] == 1) {
            $userInfo['is_idcard'] = 1;
        }
        $images = $userInfo['images'];
        $imageList = [];
        if($images && count($images) > 0) {
            foreach($images as $key => $item) {
                $category = CategoryModel::detail($item['category_id']);
                $item['name'] = $category['name'];
            }

            $userInfo['images'] = $images;
        }
        if($userInfo['grade'] && $userInfo['grade']['image_id']) {
            $gradeImage = UploadFile::detail($userInfo['grade']['image_id']);
            $userInfo['grade']['image_id'] = $gradeImage['file_path'];
            $gradeImage = UploadFile::detail($userInfo['grade']['bg_image']);
            $userInfo['grade']['bg_image'] = $gradeImage['file_path'];
        }
        $userInfo['total_friends'] = (new RelationModel)->getMyFriendCount($userInfo['user_id']); //好友数
        $userInfo['total_game']   = (new GameUsersModel)->getCreateAndJoinGame($userInfo['user_id'], true); //自己的局数
        $userInfo['is_vip']   = 0; //是否是会员 1为是 0为否
        $userLabelList = (new UserLabelModel)->getList($userInfo['user_id']);
        $setting = SettingModel::getItem('store');
        $backgroundMap = [
            'birthday' => $setting['color']['birthday'],
            'height' => $setting['color']['height'],
            'work' => $setting['color']['work'],
            'school' => $setting['color']['school'],
            'mbti' => $setting['color']['mbti'],
        ];
        $labelList = [];
        foreach($backgroundMap as $field => $color) {
            if($userInfo[$field]) {
                $value = $userInfo[$field];
                if($field == 'birthday') {
                    $value = intval((time() - strtotime($value))/(365*24*60*60)) . '岁';
                }
                if($field == 'height') {
                    $value .= 'cm';
                }
                $labelList[] = [
                    'name' => $value,
                    'background' => $color
                ];
            }
        }
        $sortLabelList = [];
        foreach($userLabelList as $key => $item) {
            $sortLabelList[] = [
                'name' => $item['label']['name'],
                'background' => $item['label']['category']['value'],
                'data_id' => $item['label']['category']['data_id'],
            ];
        }
        if(count($sortLabelList) > 0) {
            sort_array_multi($sortLabelList, ['data_id'], ['asc']);
        }
        foreach($sortLabelList as $key => $item) {
            $labelList[] = [
                'name' => $item['name'],
                'background' => $item['background'],
            ];
        }
        $userInfo['labelList']   = $labelList; //用户标签
        $gradeUsableList = GradeModel::getUsableList();
        $gradeKey = 0;
        $gradeList = [];
        foreach($gradeUsableList as $key => $item) {
            if($item['grade_id'] == $userInfo['grade_id']) {
                $gradeKey = $key;
            }
        }

        foreach($gradeUsableList as $key => $item) {
//            if($key < ($gradeKey + 3) && $key > ($gradeKey-3)) {
                $item['max_points'] = (($key + 1) == count($gradeUsableList)) ? $gradeUsableList[$key]['points'] : $gradeUsableList[$key + 1]['points'];
                $gradeList[] = $item;
//            }
        }
        $userInfo['gradeList']   = $gradeList;
        //强制加上一个是会员的标记
        $userInfo['is_pay'] = 1;

        return $this->renderSuccess(compact('userInfo'));
    }

    /**
     * 查看某个用户详情
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function frienddetail($user_id)
    {
        // 当前用户信息
        $userInfo = UserModel::detail(['user_id' => $user_id]);
        if(!$userInfo) {
            return $this->renderError('用户记录不存在');
        }
        $userInfo['is_school'] = 0;
        $userInfo['is_idcard'] = 0;
        if(isset($userInfo['school_data']) && isset($userInfo['school_data'][0]) && $userInfo['school_data'][0]['status'] == 1) {
            $userInfo['is_school'] = 1;
        }
        if(isset($userInfo['idcard_data']) && isset($userInfo['idcard_data'][0]) && $userInfo['idcard_data'][0]['status'] == 1) {
            $userInfo['is_idcard'] = 1;
        }
        $images = $userInfo['images'];
        if($images && count($images) > 0) {
            foreach($images as $key => &$item) {
                $category = CategoryModel::detail($item['category_id']);
                $item['name'] = $category['name'];
            }

            $userInfo['images'] = $images;
        }
        if($userInfo['grade'] && $userInfo['grade']['image_id']) {
            $gradeImage = UploadFile::detail($userInfo['grade']['image_id']);
            $userInfo['grade']['image_id'] = $gradeImage['file_path'];
        }
        $myInfo = $this->getUser(true);
        $relation = new RelationModel;
        $userInfo['total_friends'] = $relation->getMyFriendCount($userInfo['user_id']); //好友数
        $userInfo['total_game']   = (new GameUsersModel)->getCiGame($myInfo['user_id'], $userInfo['user_id'], true); //次的局数
        $userInfo['smallFriendsList'] = $relation->getSamllFriendList($myInfo['user_id'], $userInfo['user_id']); //我与此用户相同的关注的人
        $userInfo['relation'] = RelationModel::detail(['user_id' => $myInfo['user_id'], 'focus_user_id' => $userInfo['user_id']]);
        $userInfo['is_vip']   = 0; //是否是会员 1为是 0为否
        $userLabelList = (new UserLabelModel)->getList($userInfo['user_id']);
        $labelList = [];
        $setting = SettingModel::getItem('store');
        $backgroundMap = [
            'birthday' => $setting['color']['birthday'],
            'height' => $setting['color']['height'],
            'work' => $setting['color']['work'],
            'school' => $setting['color']['school'],
            'mbti' => $setting['color']['mbti'],
        ];
        foreach($backgroundMap as $field => $color) {
            if($userInfo[$field]) {
                $value = $userInfo[$field];
                if($field == 'birthday') {
                    $value = intval((time() - strtotime($value))/(365*24*60*60)) . '岁';
                }
                if($field == 'height') {
                    $value .= 'cm';
                }
                $labelList[] = [
                    'name' => $value,
                    'background' => $color
                ];
            }
        }
        $sortLabelList = [];
        foreach($userLabelList as $key => $item) {
            $sortLabelList[] = [
                'name' => $item['label']['name'],
                'background' => $item['label']['category']['value'],
                'data_id' => $item['label']['category']['data_id'],
            ];
        }
        if(count($sortLabelList) > 0) {
            sort_array_multi($sortLabelList, ['data_id'], ['asc']);
        }
        foreach($sortLabelList as $key => $item) {
            $labelList[] = [
                'name' => $item['name'],
                'background' => $item['background'],
            ];
        }
        $userInfo['labelList']   = $labelList; //用户标签

        return $this->renderSuccess(compact('userInfo'));
    }


    /**
     * IOSAPP 邮箱/手机号码用户自动登录
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function applogin()
    {
        $model = new UserModel;
        return $this->renderSuccess([
            'user_id' => intval($model->applogin($this->request->post())),
            'token' => $model->getToken()
        ]);
    }

    /**
     * IOSAPP 邮箱用户注册
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function appregister()
    {
        $model = new UserModel;
        return $this->renderSuccess([
            'user_id' => intval($model->appregister($this->request->post())),
            'token' => $model->getToken()
        ]);
    }

    /**
     * 安卓APP 手机用户注册
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function phoneregister()
    {
        $model = new UserModel;
        return $this->renderSuccess([
            'user_id' => intval($model->phoneregister($this->request->post())),
            'token' => $model->getToken()
        ]);
    }

    /**
     * IOSAPP 邮箱忘记密码
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function appforgetpass()
    {
        $model = new UserModel;
        return $model->appforgetpass($this->request->post()) ?  $this->renderSuccess('操作成功') : $this->renderError('操作失败');
    }

    /**
     * IOSAPP 手机号码忘记密码
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function phoneforgetpass()
    {
        $model = new UserModel;
        return $model->phoneforgetpass($this->request->post()) ?  $this->renderSuccess('操作成功') : $this->renderError('操作失败');
    }

    /**
     * 重新设置手机号码
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function updatephone()
    {
        $userInfo = $this->getUser(true);
        $post = $this->request->post();
        if(isEmpty($post['phone'])) {
            return $this->renderError('手机号码格式不对');
        }
        if(isEmpty($post['code'])) {
            return $this->renderError('验证码不能为空');
        }
        if($userInfo['phone'] == $post['phone']) {
            return $this->renderError('您已经绑定此手机号码无需更换');
        }
        $detail = UserModel::detail(['phone' => $post['phone']]);
        if($detail && $detail['user_id'] != $userInfo['user_id']) {
            return $this->renderError('此手机号码已被他人绑定');
        }
        //验证码验证
        $email = EmailModel::detail(['phone' => $post['phone'], 'name' => $post['code'], 'type' => 2]);
        if(!$email) {
            return $this->renderError('验证码错误');
        }
        if($email['status']) {
            return $this->renderError('验证码已使用');
        }
        if($email['expire_time'] < time()) {
            return $this->renderError('验证码已过期');
        }

        if($userInfo->allowField(true)->save(['phone' => $post['phone']])) {
            $email->save(['status' => 1]);

            return $this->renderSuccess('操作成功');
        }else{
            return $this->renderError('操作失败');
        }
    }


    /**
     * 重新设置邮箱
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function updateemail()
    {
        $userInfo = $this->getUser(true);
        $post = $this->request->post();
        if(!isEmail($post['email'])) {
            return $this->renderError('邮箱格式不对');
        }
        if(isEmpty($post['code'])) {
            return $this->renderError('验证码不能为空');
        }
        if($userInfo['email'] == $post['email']) {
            return $this->renderError('您已经绑定此邮箱无需更换');
        }
        $detail = UserModel::detail(['email' => $post['email']]);
        if($detail && $detail['user_id'] != $userInfo['user_id']) {
            return $this->renderError('此邮箱已被他人绑定');
        }
        //验证码验证
        $email = EmailModel::detail(['email' => $post['email'], 'name' => $post['code'], 'type' => 1]);
        if(!$email) {
            return $this->renderError('验证码错误');
        }
        if($email['status']) {
            return $this->renderError('验证码已使用');
        }
        if($email['expire_time'] < time()) {
            return $this->renderError('验证码已过期');
        }

        if($userInfo->allowField(true)->save(['email' => $post['email']])) {
            $email->save(['status' => 1]);

            return $this->renderSuccess('操作成功');
        }else{
            return $this->renderError('操作失败');
        }
    }

    /**
     * 验证手机号码
     */
    public function verifyphone()
    {
        $model = new UserModel;
        $userInfo = $this->getUser(false);
        return $model->verifyphone($this->request->post(), $userInfo) ?  $this->renderSuccess('操作成功') : $this->renderError('操作失败');
    }

    /**
     * APP用户验证邮箱真实性
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function verifyemail()
    {
        $model = new UserModel;
        return $model->verifyemail($this->request->post()) ?  $this->renderSuccess('操作成功') : $this->renderError('操作失败');
    }

    /**
     * APP用户发送邮箱验证
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function sendemail()
    {
        $param = $this->request->post();
        if(!$param['email']) {
            return $this->renderError('邮箱不能为空');
        }
        if(!isEmail($param['email'])) {
            return $this->renderError('邮箱格式不对');
        }
        if(isEmpty($param['name'])) {
            return $this->renderError('邮箱标题不能为空');
        }
        $emailDetail = EmailModel::detail(['email' => $param['email'], 'status' => 0, 'type' => 1]);
        if($emailDetail && $emailDetail['expire_time'] > time()) {
            return $this->renderError('验证码还未过期，请到您的邮箱中查看');
        }
        $code = rand(10000, 99999);
        $data = [
            'name' => $code,
            'email' => $param['email'],
            'status' => 0,
            'type' => 1,
            'expire_time' => time() + 1800,
            'wxapp_id' => $this->wxapp_id,
        ];
        $res = curl('http://chenzhou.yaqinzhizao.com/index.php/email/send', ['email' => $param['email'], 'code' => $code, 'name' => $param['name']]);
        if($res) {
            $res = json_decode($res, true);
            if($res['rs']) {
                return (new EmailModel)->save($data) ? $this->renderSuccess('操作成功') : $this->renderError('邮件发送失败，请稍后再试-3');
            }else{
                return $this->renderError('邮件发送失败，请稍后再试-2');
            }
        }else{
            return $this->renderError('邮件发送失败，请稍后再试-1');
        }

    }

    /**
     * APP用户发送手机验证码
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function sendvcode()
    {
        $param = $this->request->post();
        if(!$param['phone']) {
            return $this->renderError('手机号码不能为空');
        }
        $emailDetail = EmailModel::detail(['phone' => $param['phone'], 'status' => 0, 'type' => 2]);
        if($emailDetail && $emailDetail['expire_time'] > time()) {
            return $this->renderError('验证码还未过期，请到您的短信中查看');
        }
        $code = rand(10000, 99999);
        $data = [
            'name' => $code,
            'phone' => $param['phone'],
            'status' => 0,
            'type' => 2,
            'expire_time' => time() + 1800,
            'wxapp_id' => $this->wxapp_id,
        ];
        $smsConfig = SettingModel::getItem('sms', $this->wxapp_id);
        $smsConfig['engine']['aliyun']['order_pay']['accept_phone'] = $param['phone'];
        $SmsDriver = new SmsDriver($smsConfig);
        $res = $SmsDriver->sendSms('order_pay', ['code' => $code]);
        if($res) {
            return (new EmailModel)->save($data) ? $this->renderSuccess('短信发送成功') : $this->renderError('短信发送失败，请稍后再试-1');
        }else{
            return $this->renderError('短信发送失败，请稍后再试-2');
        }
    }

    /**
     * 更新用户基本信息
     */
    public function editbase()
    {
        $userInfo = $this->getUser(true);
        $post = $this->request->post();
        if(isEmpty($post['nickName'])) {
            return $this->renderError('名字不能为空');
        }
        if(isEmpty($post['gender'])) {
            return $this->renderError('性别未选');
        }
        if(isEmpty($post['birthday'])) {
            return $this->renderError('生日未选');
        }
        if(isEmpty($post['avatarUrl'])) {
            return $this->renderError('头像未选');
        }
        if(isEmpty($post['mbti'])) {
            return $this->renderError('mbti不能为空');
        }
        //主动设置背景图片
        $category = CategoryModel::getSubCategoryByIdentifier('picture-cat');
        $category_id = 39;

        $model = new ImagesModel;
        $imageDetail = ImagesModel::detail(['category_id' => $category_id, 'user_id' => $userInfo['user_id']]);
        if(!$imageDetail) {
            $model->add(['category_id' => $category_id, 'image_id' => $post['avatarUrl'], 'user_id' => $userInfo['user_id']]);
        }


        return $userInfo->allowField(true)->save($post) ? $this->renderSuccess('操作成功') : $this->renderError('操作失败');
    }

    /**
     * 更新用户其他信息(出生年月、身高、职业、学校、个人简介)
     */
    public function editother()
    {
        $userInfo = $this->getUser(true);
        $post = $this->request->post();
        if(isEmpty($post['birthday'])) {
            return $this->renderError('出生年月未选');
        }
        if(isEmpty($post['height'])) {
            return $this->renderError('身高不能为空');
        }
        if(isEmpty($post['work'])) {
            return $this->renderError('职业不能为空');
        }
        if(isEmpty($post['school'])) {
            return $this->renderError('学校不能为空');
        }

        return $userInfo->allowField(true)->save($post) ? $this->renderSuccess('操作成功') : $this->renderError('操作失败');
    }

    /**
     * 更新头像和昵称
     */
    public function editnickname()
    {
        $userInfo = $this->getUser(true);
        $post = $this->request->post();
        if(isEmpty($post['nickName'])) {
            return $this->renderError('名字不能为空');
        }
        if(isEmpty($post['avatarUrl'])) {
            return $this->renderError('头像未选');
        }

        return $userInfo->allowField(true)->save($post) ? $this->renderSuccess('操作成功') : $this->renderError('操作失败');
    }

    /**
     * 用户标签选择列表
     */
    public function label()
    {
        $userInfo = $this->getUser(true);
        $list = (new LabelModel)->getList($userInfo['user_id']);

        return $this->renderSuccess(compact('list'));
    }

    /**
     * 保存用户的标签
     */
    public function addlabel()
    {
        $user = $this->getUser(true);
        $post = $this->request->post();
        if(isEmpty($post['label_id'])) {
            return $this->renderError('至少选择一个标签');
        }
        $list = explode(',', $post['label_id']);
        $res = (new UserLabelModel)->add($user['user_id'], $list);

        return $res ? $this->renderSuccess('操作成功') : $this->renderError('操作失败');
    }

    /**
     * 注销用户
     */
    public function logout()
    {
        $user = $this->getUser(true);
        if(!$user) {
            return $this->renderError('用户不存在');
        }
        $res = $user->save(['is_delete' => 1]);

        return $res ? $this->renderSuccess('操作成功') : $this->renderError('操作失败');
    }

    /**
     * 苹果支付的回调
     */
    public function iphonepay()
    {
        $user = $this->getUser(true);
        if(!$user) {
            return $this->renderError('用户不存在');
        }
        $param = $this->request->param();
        $goods = GoodsModel::detail($param['goods_id']);
        if(!$goods) {
            return $this->renderError('商品不存在');
        }
        $goodsSku = GoodsSkuModel::get(['goods_id' => $param['goods_id']]);
        $day = $goodsSku['goods_weight'];
        $res = $user->save([
            'is_pay' => 1,
            'pay_type'=> 2,
            'pay_time' => date('Y-m-d H:i:s', time()),
            'pay_start_date' => date('Y-m-d', time()),
            'pay_end_date' => date('Y-m-d', time() + $day * 86400)
        ]);

        return $res ? $this->renderSuccess('操作成功') : $this->renderError('操作失败');
    }


    /**
     * 苹果支付的回调
     */
    public function isvip()
    {
        $is_vip = 1;

        return $this->renderSuccess(compact('is_vip'));
    }

}
