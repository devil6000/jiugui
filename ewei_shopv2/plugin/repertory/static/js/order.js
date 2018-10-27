define(['core', 'tpl'], function(core, tpl) {
    var modal = {
        page: 1,
    };
    modal.init = function() {
        $('.fui-content').infinite({
            onLoading: function() {
                modal.getList()
            }
        });
        if (modal.page == 1) {
            modal.getList()
        }
    };
    modal.loading = function() {
        modal.page++
    };
    modal.getList = function() {
        core.json('repertory/index/get_list', {
            page: modal.page,
        }, function(ret) {
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
            $('.content-loading').hide();
            modal.page++;
            core.tpl('#container', 'tpl_groups_order_list', result, modal.page > 1);
            FoxUI.according.init();
            require(['../addons/ewei_shopv2/plugin/repertory/static/js/op.js'], function(modal) {
                modal.init({
                    fromDetail: false
                })
            })
        })
    };
    return modal
});