$(document).ready(function(){
	$('form').each(function(){
		var tag='#'+$(this).attr('id');
		checkform({
			formid:tag,
		},function(ret){
			valert({status:ret.status,msg:ret.msg});
		});
	});
});