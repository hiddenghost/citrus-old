<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">序列号列表</div>
                </div>
                <div class="page_toolbar am-margin-bottom-xs am-cf">
                    <form id="form-search" class="toolbar-form" action="">
                        <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                        <div class="am-u-sm-12 am-u-md-9">
                            <div class="am fr">
                                <div class="am-form-group am-fl">
                                    <select name="status"  data-am-selected="{btnSize: 'sm', placeholder: '状态'}">
                                        <option value="1" <?php if($status == 1): ?> selected="selected" <?php endif;?>>待发货</option>
                                        <option value="2" <?php if($status == 2): ?> selected="selected" <?php endif;?>>已发货</option>
                                    </select>
                                </div>
                                <div class="am-form-group am-fl">

                                    <div class="am-input-group am-input-group-sm tpl-form-border-form">

                                        <input type="text" class="am-form-field" name="order_no"
                                               placeholder="请输入序列号" value="">
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
                <div class="widget-body am-fr">

                    <div class="am-u-sm-12 am-scrollable-horizontal">
                        <table width="100%"
                               class="am-table am-table-compact am-table-striped tpl-table-black am-text-nowrap">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>序列号</th>
                                <th>手机号</th>
                                <th>姓名</th>
                                <th>收货地址</th>
                                <th>核销码</th>
                                <th>状态</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): ?>
                                <?php foreach ($list as $item): ?>
                                    <tr>
                                        <td class="am-text-middle"><?= $item['id'] ?></td>
                                        <td class="am-text-middle"><?= $item['order_no'] ?></td>
                                        <td class="am-text-middle"><?= $item['phone'] ?></td>
                                        <td class="am-text-middle"><?= $item['address']['name'] ?></td>
                                        <td class="am-text-middle"><?= $item['address']['address'] ?></td>
                                        <td class="am-text-middle"><?= $item['write_off_num'] ?></td>
                                        <td class="am-text-middle"><?= $item['statusInfo'] ?></td>
                                        <td class="am-text-middle">
                                            <div class="tpl-table-black-operation">
                                                <?php if (checkPrivilege('market.gift/edit')): ?>
                                                    <?php if($item['status'] == 1):?>
                                                    <a class="recevieBtn" data-href="<?= url('market.gift/setstatus', ['id' => $item['id']]) ?>">
                                                        <i class="am-icon-floppy-o "></i> 发货
                                                    </a>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="11" class="am-text-center">暂无记录</td>
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
<script>
    $(function () {

        $(".recevieBtn").on('click',function(){
            var href = $(this).attr("data-href")
            $.post(href,{},function(res){
                layer.alert(res.msg)
            })
        })
    });
</script>

