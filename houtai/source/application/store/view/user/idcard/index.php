<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf"> 实名认证列表</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom am-cf">
                        <form class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="am-u-sm-12 am-u-md-12">
                                <div class="am fr">
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="search"
                                                   placeholder="请输入企业名称/申请人/申请人电话"
                                                   value="<?= $request->get('search') ?>">
                                            <div class="am-input-group-btn">
                                                <button class="am-btn am-btn-default am-icon-search"
                                                        type="submit"></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="am-scrollable-horizontal am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black am-text-nowrap">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>申请人</th>
                                <th>身份证姓名</th>
                                <th>身份证号码</th>
                                <th>身份证正面图片</th>
                                <th>身份证反面图片</th>
                                <th>照片墙图片</th>
                                <th>认证时间</th>
                                <th>认证结果说明</th>
                                <th>认证状态</th>
                                <th>创建时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['idcard_id'] ?></td>
                                    <td class="am-text-middle">
                                        <p><?= $item['user']['nickName'] ?></p>
                                    </td>
                                    <td class="am-text-middle"><?= $item['name'] ?></td>
                                    <td class="am-text-middle"><?= $item['code'] ?></td>
                                    <td class="goods-detail am-text-middle">
                                        <div class="goods-image">
                                            <a href="<?= $item['idcard_zheng'] ?>" title="点击查看大图" target="_blank">
                                                <img src="<?= $item['idcard_zheng'] ?>" alt="">
                                            </a>
                                        </div>
                                    </td>
                                    <td class="goods-detail am-text-middle">
                                        <div class="goods-image">
                                            <a href="<?= $item['idcard_fan'] ?>" title="点击查看大图" target="_blank">
                                                <img src="<?= $item['idcard_fan'] ?>" alt="">
                                            </a>
                                        </div>
                                    </td>
                                    <td class="goods-detail am-text-middle">
                                        <div class="goods-image">
                                            <?php foreach($item['user']['images'] as $image) { ?>
                                            <a href="<?= $image['image_id'] ?>" title="点击查看大图" target="_blank">
                                                <img src="<?= $image['image_id'] ?>" alt="">
                                            </a>
                                            <?php } ?>
                                        </div>
                                    </td>
                                    <td class="am-text-middle"><?= $item['check_time'] ?></td>
                                    <td class="am-text-middle"><?= $item['check_result'] ?></td>
                                    <td class="am-text-middle">
                                        <span class="am-badge am-badge-success">
                                            <?php if($item['status'] < 1) { ?>
                                                待认证
                                            <?php }elseif($item['status'] == 1) { ?>
                                                认证成功
                                            <?php }else{ ?>
                                                认证失败
                                            <?php } ?>
                                        </span>
                                    </td>
                                    <td class="am-text-middle"><?= $item['create_time'] ?></td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('user.idcard/check') && $item['status'] < 1): ?>

                                                <a href="javascript:void(0);"
                                                   class="j-grade tpl-table-black-operation-default"
                                                   data-id="<?= $item['idcard_id'] ?>"
                                                >
                                                    <i class="am-icon-pencil"></i> 审核
                                                </a>

                                            <?php endif; ?>
                                            <?php if (checkPrivilege('user.idcard/delete')): ?>
                                                <a href="javascript:void(0);"
                                                   class="item-delete tpl-table-black-operation-default"
                                                   data-id="<?= $item['idcard_id'] ?>">
                                                    <i class="am-icon-trash"></i> 删除
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="10" class="am-text-center">暂无记录</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="am-u-lg-12 am-cf">
                        <div class="am-fr"><?= $list->render() ?> </div>
                        <div class="am-fr pagination-total am-margin-right">
                            <div class="am-vertical-align-middle">总记录：<?= $list->total() ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 模板：修改会员等级 -->
<script id="tpl-grade" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        审核状态
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                        <select name="apply[status]"
                                data-am-selected="{btnSize: 'sm', placeholder: '请选择审核状态'}">
                                <option value="1">审核通过</option>
                                <option value="2">审核不通过</option>
                        </select>
                    </div>
                </div>
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label"> 审核意见 </label>
                    <div class="am-u-sm-8 am-u-end">
                                <textarea rows="2" name="apply[check_result]" placeholder="请输入审核意见"
                                          class="am-field-valid"></textarea>
                    </div>
                </div>
            </div>
        </form>
    </div>
</script>

<script>
    $(function () {

        /**
         * 审核
         */
        $('.j-grade').on('click', function () {
            var data = $(this).data();
            $.showModal({
                title: '流程审核'
                , area: '460px'
                , content: template('tpl-grade', data)
                , uCheck: true
                , success: function ($content) {
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('user.idcard/check') ?>',
                        data: {idcard_id: data.id}
                    });
                    return true;
                }
            });
        });



        // 删除元素
        var url = "<?= url('user.idcard/delete') ?>";
        $('.item-delete').delete('idcard_id', url);

    });
</script>

