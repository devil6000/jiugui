define(['core', 'tpl'], function(core, tpl) {
    var modal = {
        id: ''
    };
    modal.init = function(params) {
    	modal.id = params.id;
        $("#submit").click(function() {
            modal.submit()
        });
    };
    modal.submit = function() {
        core.json('sale/extract/check/submit', {
            id: modal.id
        }, function(ret) {
        	console.log(ret);
        	if(ret.status==0){
                FoxUI.toast.show(ret.result.message);
                return
        	}
            var result = ret.result;
            FoxUI.toast.show("核销成功！");
            setTimeout(function(){
            	location.reload() 
            },1000);
        }, true, true)
    };
    return modal
});