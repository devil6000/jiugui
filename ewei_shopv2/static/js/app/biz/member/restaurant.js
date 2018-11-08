define(['core', 'tpl'], function(core, tpl) {
    var modal = {

    };
    modal.init = function() {
        $('#btn-next').click(function() {
            var store_name = $.trim($('#store_name').val());
            var contacts = $.trim($('#contacts').val());
            var tel = $.trim($('#tel').val());

            if ($(this).attr('submit')) {
                return
            }
            if($.isEmpty(store_name)){
                FoxUI.toast.show('请输入店铺名称');
                return;
            }
            if($.isEmpty(contacts)){
                FoxUI.toast.show('请输入联系人');
                return;
            }
            if($.isEmpty(tel)){
                FoxUI.toast.show('请输入电话');
                return;
            }

            $(this).attr('submit', '1');
            core.json('member/restaurant/apply', {
                store_name: store_name,
                contacts: contacts,
                tel: tel
            }, function(rjson) {
                if (rjson.status != 1) {
                    $('#btn-next').removeAttr('submit');
                    FoxUI.toast.show(rjson.result);
                    return
                }else{
                    FoxUI.toast.show(rjson.result.message);
                    location.href = core.getUrl('member');
                    return;
                }
            }, true, true)
        });
    };
    return modal
});