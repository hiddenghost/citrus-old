<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title a m-cf">推送任务列表</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <div class="am-form-group">
                            <?php if (checkPrivilege('user.pushtask/add')): ?>
                                <div class="am-btn-group am-btn-group-xs">
                                    <a class="am-btn am-btn-default am-btn-success"
                                       href="<?= url('user.pushtask/add') ?>">
                                        <span class="am-icon-plus"></span> 新增
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="am-scrollable-horizontal am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black am-text-nowrap">
                            <thead>
                            <tr>
                                <th>推送任务ID</th>
                                <th>推送标题</th>
                                <th>推送内容</th>
                                <th>关联局</th>
                                <th>推送用户</th>
                                <th>推送类型</th>
                                <th>推送标签</th>
                                <th>推送状态</th>
                                <th>创建时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $typeMap = ['1' => '某些用户', '2' => '全部用户', '3' => '标签用户']; ?>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['push_task_id'] ?></td>
                                    <td class="am-text-middle"><?= $item['title'] ?></td>
                                    <td class="am-text-middle" ><?= $item['content'] ?></td>
                                    <td class="am-text-middle" ><?= $item['game']['name'] ?></td>
                                    <td class="am-text-middle" >
                                        <div style="width: 300px;white-space: normal;word-wrap: break-word;"><?= $item['usernames'] ?></div>
                                    </td>
                                    <td class="am-text-middle"><?= $typeMap[$item['type']] ?></td>
                                    <td class="am-text-middle">
                                        <?= $item['tag']['name']  ?>
                                    </td>
                                    <td class="am-text-middle"><?= $item['status'] > 0 ? '已推送' : '待推送' ?></td>
                                    <td class="am-text-middle"><?= $item['create_time'] ?></td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if($item['type'] != 2): ?>
                                                <a href="javascript:void(0);"
                                                   class="item-push "
                                                   data-id="<?= $item['push_task_id'] ?>"
                                                >
                                                    <i class="am-icon-pencil"></i> 推送
                                                </a>
                                            <?php endif; ?>
                                            <?php if($item['type'] == 2): ?>
                                                <a href="javascript:void(0);"
                                                   class="item-push-all "
                                                   data-id="<?= $item['push_task_id'] ?>"
                                                   data-page="<?= $item['page'] ?>"
                                                >
                                                    <i class="am-icon-pencil"></i> 推送
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('user.pushtask/edit')): ?>
                                                <a href="<?= url('user.pushtask/edit', ['push_task_id' => $item['push_task_id']]) ?>">
                                                    <i class="am-icon-pencil"></i> 编辑
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('user.pushtask/delete')): ?>
                                                <a href="javascript:void(0);"
                                                   class="item-delete tpl-table-black-operation-del"
                                                   data-id="<?= $item['push_task_id'] ?>">
                                                    <i class="am-icon-trash"></i> 删除
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="8" class="am-text-center">暂无记录</td>
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

<!-- 模板：推送全部用户 -->
<script id="tpl-grade" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
                <div>推送共<?= $total_page ?>页</div>
                <div id="push-run" style="font-size:12px;height: 200px;overflow-y: scroll;">

                </div>
            </div>
        </form>
    </div>
</script>

<script>
    $(function () {

        // 删除元素
        var url = "<?= url('user.pushtask/delete') ?>";
        $('.item-delete').delete('push_task_id', url, '删除后不可恢复，确定要删除吗？');

        // 推送元素
        var pushUrl = "<?= url('user.pushtask/push') ?>";
        $('.item-push').delete('push_task_id', pushUrl, '确定要推送吗？');

        /**
         * 修改会员等级
         */
        $('.item-push-all').on('click', function () {
            var data = $(this).data();

            $.showModal({
                title: '推送全部用户'
                , area: '660px'
                , content: template('tpl-grade', data)
                , uCheck: true
                , btn: ['开始推送', '取消']
                , success: function ($content) {
                }
                , yes: function ($content) {
                    console.log('开始推送');
                    var total_page = '<?= $total_page ?>';
                    var pushUrl = "<?= url('user.pushtask/pushall') ?>";
                    var page = data.page;
                    tpl = '<div>开始推送</div>';
                    $content.find('#push-run').append(tpl);
                    var interval = setInterval(function() {
                        console.log(page, total_page, 11);
                        if(page > total_page) {
                            clearInterval(interval);
                            return;
                        }
                        $.post(pushUrl, {push_task_id: data.id, page: page}, function(res){
                            console.log(res)
                            tpl = '<div>第' + (page - 1) + '页' + res.data + '个用户已推送</div>';
                            if(res.code == 1) {
                                $content.find('#push-run').append(tpl);
                            }
                        })
                        page += 1;
                    }, 5000);
                }
            });
        });

    });
</script>

