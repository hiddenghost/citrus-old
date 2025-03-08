<?php

use app\common\enum\DeliveryType as DeliveryTypeEnum;

// 订单详情
$detail = isset($detail) ? $detail : null;
?>
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget__order-detail widget-body am-margin-bottom-lg">

                    <!-- 基本信息 -->
                    <div class="widget-head am-cf">
                        <div class="widget-title am-fl">基本信息</div>
                    </div>
                    <div class="am-scrollable-horizontal">
                        <table class="regional-table am-table am-table-bordered am-table-centered
                            am-text-nowrap am-margin-bottom-xs">
                            <tbody>
                            <tr>
                                <th>昵称</th>
                                <th>头像</th>
                                <th>手机号码</th>
                                <th>邮箱</th>
                                <th>会员等级</th>
                                <th>性别</th>
                                <th>城市</th>
                                <th>生日</th>
                                <th>mbti</th>
                                <th>认证状态</th>
                                <th>注册情况</th>
                            </tr>
                            <tr>
                                <td><?= $detail['nickName'] ?>(用户id：<?= $detail['user_id'] ?>)</td>
                                <td>
                                    <img src="<?= $detail['avatarUrl'] ?>" style="width: 60px;height: 60px">
                                </td>
                                <td><?= $detail['phone'] ?></td>
                                <td><?= $detail['email'] ?></td>
                                <td><?= $detail['grade']['name'] ?></td>
                                <td><?= $detail['gender'] ?></td>
                                <td><?= $detail['region']['country'] .'/'. $detail['region']['province'] .'/'. $detail['region']['city'] ?></td>
                                <td><?= $detail['birthday'] ?></td>
                                <td><?= $detail['mbti'] ?></td>
                                <td>
                                    <p>邮箱/手机认证：<?= $detail['status'] == 1 ? '已认证' : '待认证' ?></p>
                                    <p>实名认证：<?= $detail['is_idcard'] == 1 ? '已认证' : '待认证' ?></p>
                                </td>
                                <td>
                                    <p>注册方式：
                                        <?php $typeMap = ['1' => '邮箱', '2' => '手机号码', '3' => 'IOS', '4' => '微信']; ?>
                                        <?= $typeMap[$detail['register_type']] ?>
                                    </>
                                    <p>注册时间：<?= $detail['create_time'] ?></p>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- 学校信息 -->
                    <div class="widget-head am-cf">
                        <div class="widget-title am-fl">其他信息</div>
                    </div>
                    <div class="am-scrollable-horizontal">
                        <table class="regional-table am-table am-table-bordered am-table-centered
                            am-text-nowrap am-margin-bottom-xs">
                            <tbody>
                            <tr>
                                <th>工作</th>
                                <th>学校</th>
                                <th>身高</th>
                                <th>简介</th>
                                <th>标签</th>
                            </tr>
                            <tr>
                                <td><?= $detail['work'] ?></td>
                                <td><?= $detail['school'] ?></td>
                                <td><?= $detail['height'] ?>cm</td>
                                <td><?= $detail['desc'] ?></td>
                                <td>
                                    <?php foreach($detail['labelData'] as $item) { ?>
                                    <span class="am-badge am-badge-secondary"><?= $item['label']['name'] ?></span>
                                    <?php } ?>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>





                </div>
            </div>

        </div>
    </div>
</div>

<script>
    $(function () {



        /**
         * 表单验证提交
         * @type {*}
         */
        $('.my-form').superForm();

    });
</script>
