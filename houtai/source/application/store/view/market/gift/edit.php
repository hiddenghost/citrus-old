<link rel="stylesheet" href="assets/common/plugins/umeditor/themes/default/css/umeditor.css">
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">编辑礼品券</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">（礼品券/产品）名称 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="gift[name]"
                                           value="<?= $model['name'] ?>" placeholder="请输入礼品券名称" required>
                                    <small>例如：##商品礼品兑换券</small>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">序列号前缀 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="gift[number_prefix]"
                                           value="<?= $model['number_prefix'] ?>" placeholder="字母或数字组合，最多6位" readonly required>
                                </div>
                            </div>
                            <div class="am-form-group switch-expire_type expire_type__20">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">有效截至时间 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="j-laydate-start am-form-field"
                                           name="gift[end_time]"
                                           value="<?= date('Y-m-d h:i:s',$model['end_time']) ?>"
                                           placeholder="点击选择日期"
                                           required>

                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">产品图 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="am-form-file">
                                        <div class="am-form-file">
                                            <button type="button"
                                                    class="upload-file am-btn am-btn-secondary am-radius">
                                                <i class="am-icon-cloud-upload"></i> 选择图片
                                            </button>
                                            <div class="uploader-list am-cf">
                                                <div class="file-item">
                                                    <a href="<?= $pic ?>" title="点击查看大图"
                                                       target="_blank">
                                                        <img src="<?= $pic ?>">
                                                    </a>
                                                    <input type="hidden" name="gift[image_id]"
                                                           value="<?= $model['image_id'] ?>">
                                                    <i class="iconfont icon-shanchu file-item-delete"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group am-padding-top">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">产品详情 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <!-- 加载编辑器的容器 -->
                                    <textarea id="container" name="gift[content]"
                                              type="text/plain"><?= $model['content'] ?></textarea>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">发放总数量 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="number" min="1" class="tpl-form-input" name="gift[total_num]"
                                           value="<?= $model['total_num'] ?>" readonly required>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                    <button type="submit" class="j-submit am-btn am-btn-secondary">提交
                                    </button>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<script>
    $(function () {

        // swith切换
        var $mySwitch = $('[data-x-switch]');
        $mySwitch.find('[data-switch-item]').click(function () {
            var $mySwitchBox = $('.' + $(this).data('switch-box'));
            $mySwitchBox.hide().filter('.' + $(this).data('switch-item')).show();
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>

<!-- 图片文件列表模板 -->
<script id="tpl-file-item" type="text/template">
    {{ each list }}
    <div class="file-item">
        <a href="{{ $value.file_path }}" title="点击查看大图" target="_blank">
            <img src="{{ $value.file_path }}">
        </a>
        <input type="hidden" name="{{ name }}" value="{{ $value.file_id }}">
        <i class="iconfont icon-shanchu file-item-delete"></i>
    </div>
    {{ /each }}
</script>
<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}
<script src="assets/common/plugins/umeditor/umeditor.config.js?v=<?= $version ?>"></script>
<script src="assets/common/plugins/umeditor/umeditor.min.js"></script>
<script src="assets/common/js/vue.min.js?v=<?= $version ?>"></script>
<script src="assets/common/plugins/laydate/laydate.js"></script>
<script src="assets/store/js/select.data.js?v=<?= $version ?>"></script>
<script>
    $(function () {

        // 选择图片
        $('.upload-file').selectImages({
            name: 'gift[image_id]'
        });

        // 富文本编辑器
        UM.getEditor('container', {
            initialFrameWidth: 375 + 15,
            initialFrameHeight: 600
        });
        // 日期选择器
        laydate.render({
            elem: '.j-laydate-start'
            , type: 'datetime'
        });
        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
