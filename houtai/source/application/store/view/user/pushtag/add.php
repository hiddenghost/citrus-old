<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">添加推送标签</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 推送标签名称 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="post[name]"
                                           placeholder="请输入推送标签名称" required>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">推送标签描述 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="post[description]"
                                           value="" required>
                                </div>
                            </div>

                            <div class="am-form-group" id="type_1">
                                <label class="am-u-sm-3  am-u-lg-2 am-form-label "> 选择用户 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="widget-become-goods am-form-file am-margin-top-xs">
                                        <button type="button"
                                                class="j-selectUser upload-file am-btn am-btn-secondary am-radius">
                                            <i class="am-icon-cloud-upload"></i> 选择用户
                                        </button>
                                        <div class="user-list uploader-list am-cf">
                                        </div>
                                    </div>
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

<!-- 图片文件列表模板 -->
<script id="tpl-user-item" type="text/template">
    {{ each $data }}
    <div class="file-item">
        <a href="{{ $value.avatarUrl }}" title="{{ $value.nickName }} (ID:{{ $value.user_id }})" target="_blank">
            <img src="{{ $value.avatarUrl }}">
            <div style="font-size: 10px;">{{ $value.nickName }}</div>
        </a>
        <input type="hidden" name="post[user_id][]" value="{{ $value.user_id }}">
    </div>
    {{ /each }}
</script>

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}

<script src="assets/store/js/select.region.js?v=1.2"></script>
<script src="assets/store/js/select.data.js?v=<?= $version ?>"></script>
<script>

    $(function () {

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

        // 选择用户
        $('.j-selectUser').click(function () {
            var $userList = $('.user-list');
            $.selectData({
                title: '选择用户',
                uri: 'user/lists',
                dataIndex: 'user_id',
                done: function (data) {
                    console.log(data, 111)
                    var user = data;
                    $userList.html(template('tpl-user-item', user));
                }
            });
        });

    });
</script>
