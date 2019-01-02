define(['core', 'tpl'], function(core, tpl, op) {
    var modal = {
        params: {}
    };
    modal.init = function() {
        $(".fui-number").numbers({
            minToast: "最少核销{min}次",
            maxToast: "最多核销{max}次"
        });
        $('.order-verify').click(function() {
            modal.verify($(this))
        })
    };
    modal.verify = function(btn) {
        var tip = "",
            orderid = btn.data('orderid');
        var times = parseInt($('.shownum').val());
        if (times <= 0) {
            FoxUI.toast.show('最少核销一次');
            return;
        }
        tip = "确认核销 <span class='text-danger'>" + times + "</span> 次吗?";

        FoxUI.confirm(tip, function() {
            core.json('repertory/verify/complete', {
                id: orderid,
                times: times
            }, function(ret) {
                if (ret.status == 0) {
                    FoxUI.toast.show(ret.result.message);
                    return
                }
                location.href = core.getUrl('repertory/verify/success', {
                    id: orderid,
                    times: times
                })
            })
        })
    };
    return modal
});