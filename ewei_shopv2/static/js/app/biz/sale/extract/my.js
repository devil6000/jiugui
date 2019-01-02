define(['core', 'tpl'], function(core, tpl) {
    var modal = {
        page: 1,
        status: 1
    };
    modal.init = function(params) {
        $("#cateTab a").click(function() {
            var status = $(this).data('status');
            modal.status = status;
            modal.page = 1;
            $(this).addClass('active').siblings().removeClass('active');
            FoxUI.loader.show('mini');
            $("#container").html('');
            modal.getList(modal.status)
        });
        $('.fui-content').infinite({
            onLoading: function() {
                modal.getList(modal.status)
            }
        });
        if (modal.page == 1) {
            modal.getList(modal.status)
        }
    };
    modal.getList = function(status) {
        core.json('sale/extract/my/getlist', {
            page: modal.page,
            status: status
        }, function(ret) {
            $('.infinite-loading').hide();
            var result = ret.result;
            if (result.total <= 0) {
                $('.content-empty').show();
                $('.fui-content').infinite('stop')
            } else {
                $('.content-empty').hide();
                $('.fui-content').infinite('init');
                if (result.list.length <= 0 || result.list.length < result.pagesize) {
                    $('.fui-content').infinite('stop')
                }
            }
            modal.page++;
            FoxUI.loader.hide();
            core.tpl('#container', 'tpl_list_coupon_my', result, modal.page > 1)
        })
    };
    return modal
});