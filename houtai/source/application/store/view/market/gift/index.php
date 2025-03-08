<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">礼品券列表</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="tips am-margin-bottom-sm am-u-sm-12">
                        <div class="pre">
                            <p> 注：礼品券可以兑换商品</p>
                            <p> 领取连接：<?= $linkurl ?></p>
                        </div>
                        <div class="am-form-group" style="border-top: 1px solid green">
                            <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post" action="<?= url('giftconfig') ?>">
                            <input name="name" value="banner" type="hidden">
                            <label class="am-u-sm-3 am-u-lg-3 am-form-label form-require">领取首页banner图(建议尺寸4:3) </label>
                            <div class="am-u-sm-9 am-u-end" style="margin-top: 25px">
                                <div class="am-form-file">
                                    <div class="am-form-file">
                                        <button type="button"
                                                class="upload-file am-btn am-btn-secondary am-radius">
                                            <i class="am-icon-cloud-upload"></i> 选择图片
                                        </button>
                                        <div class="uploader-list am-cf">
                                            <?php if(isset($bannerConfig['value'])):?>
                                            <div class="file-item">
                                                <a href="<?= $bannerConfig['banner_pic'] ?>" title="点击查看大图"
                                                   target="_blank">
                                                    <img src="<?= $bannerConfig['banner_pic'] ?>">
                                                </a>
                                                <input type="hidden" name="value"
                                                       value="<?=$bannerConfig['value'] ?>">
                                                <i class="iconfont icon-shanchu file-item-delete"></i>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <button style="margin: 25px" type="" id="saveBanner" class="am-btn am-btn-secondary am-radius">保存</button>
                                    </div>
                                </div>
                            </div>
                            </form>
                        </div>
                    </div>

                        <div class="am-form-group">
                            <div class="am-btn-toolbar">
                                <?php if (checkPrivilege('market.gift/add')): ?>
                                    <div class="am-btn-group am-btn-group-xs">
                                        <a class="am-btn am-btn-default am-btn-success am-radius"
                                           href="<?= url('market.gift/add') ?>">
                                            <span class="am-icon-plus"></span> 新增
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="am-u-sm-12 am-scrollable-horizontal">
                        <table width="100%"
                               class="am-table am-table-compact am-table-striped tpl-table-black am-text-nowrap">
                            <thead>
                            <tr>
                                <th>礼品券ID</th>
                                <th>(礼品券/产品)名称</th>
                                <th>发放总数量</th>
                                <th>添加时间</th>
                                <th>截至时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): ?>
                                <?php foreach ($list as $item): ?>
                                    <tr>
                                        <td class="am-text-middle"><?= $item['id'] ?></td>
                                        <td class="am-text-middle"><?= $item['name'] ?></td>
                                        <td class="am-text-middle"><?= $item['total_num'] ?></td>

                                        <td class="am-text-middle"><?= $item['create_time'] ?></td>
                                        <td class="am-text-middle"><?= date("Y-m-d H:i:s",$item['end_time']) ?></td>
                                        <td class="am-text-middle">
                                            <div class="tpl-table-black-operation">
                                                <?php if (checkPrivilege('market.gift/edit')): ?>
                                                    <a href="<?= url('market.gift/edit', ['id' => $item['id']]) ?>">
                                                        <i class="am-icon-pencil"></i> 编辑
                                                    </a>
                                                    <a href="<?= url('market.gift/numlist', ['id' => $item['id']]) ?>">
                                                        <i class="am-icon-floppy-o"></i> 查看序列号
                                                    </a>
                                                    <a href="<?= url('market.gift/export', ['id' => $item['id']]) ?>">
                                                        <i class="am-icon-floppy-o"></i> 导出序列号
                                                    </a>
                                                    <a href="<?= url('market.gift/receive', ['id' => $item['id']]) ?>">
                                                        <i class="am-icon-floppy-o"></i> 领取记录
                                                    </a>
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
<!-- 图片文件列表模板 -->
<script id="tpl-file-item" type="text/template">
    {{ each list }}
    <div class="file-item">
        <a href="{{ $value.file_path }}" title="点击查看大图" target="_blank">
            <img src="{{ $value.file_path }}">
        </a>
        <input type="hidden" name="{{ name }}" id="bannerImgId" value="{{ $value.file_id }}">
        <i class="iconfont icon-shanchu file-item-delete"></i>
    </div>
    {{ /each }}
</script>
<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}
<script>
    $(function () {

        // 选择图片
        $('.upload-file').selectImages({
            name: 'value'
        });
       $('#my-form').superForm();
    })
</script>

