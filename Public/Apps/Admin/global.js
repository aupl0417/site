$(document).ready(function(){
	var thisPage=$('.nav-box2').attr('data-page');

	$('.nav-box2 a[href="'+thisPage+'"]').addClass('active');
	if($('.nav-box2 li:last a').html()=='[title]') $('.nav-box2 li:last').addClass('hide');
	
	
	
	$('[data-type="ajax-status"]').click(function(){
		var d=$(this).data();
		var obj=$(this);
		
		ajax_post({
			url:d.url,
			data:d
		},function(ret){
			if(ret.status=='success'){
				obj.html(ret.text).attr('value',ret.value);
				if(obj.hasClass('btn-default')) obj.removeClass('btn-default').addClass('btn-success');
				else obj.removeClass('btn-success').addClass('btn-default');
			}else{
				valert({status:ret.status,msg:ret.msg});
			}
		});
		
	});
	
	if($('.hover-search').size()>0){
		$('.hover-search').click(function(){
			$(this).find('.hidetag').toggleClass('hidetag');
		});
	}
	
	if($('.edit-area').size()>0){
		$('.edit-area').each(function(){
			var id=$(this).attr('id');
			editAreaLoader.init({
				id: id	// id of the textarea to transform		
				,start_highlight: true	// if start with highlight
				,allow_resize: "both"
				,allow_toggle: true
				,word_wrap: true
				,language: "zh"
				,syntax: "html"
				,min_height: 350
			});
			
		});
	}	
	
	//上传图片
	if($('[data-type="upload-images"]').size()>0){
		$('[data-type="upload-images"]').click(function(){
			var d=$(this).data();
			if($('.vform-action').size()>0){				
				vmodal({
					tag:'#ajax-modal2',
					title:d.label,
					iframe:'/UploadOne/index/field/'+d.name,
					width:'95%',				
				},function(){
					$('.vform-action').html(d.name);
				});
			}else{
				valert({status:'warning',msg:'请在页面上添加Class为vform-action的隐藏标签才可正常上传图片'});
			}
			
		});
	}
	$('.minicolors').each(function() {
        $(this).minicolors({
            control: $(this).attr('data-control') || 'hue',
            defaultValue: $(this).attr('data-defaultValue') || '',
            inline: $(this).attr('data-inline') === 'true',
            letterCase: $(this).attr('data-letterCase') || 'lowercase',
            opacity: $(this).attr('data-opacity'),
            position: $(this).attr('data-position') || 'bottom left',
            change: function(hex, opacity) {
                if (!hex) return;
                if (opacity) hex += ', ' + opacity;
                if (typeof console === 'object') {
                    console.log(hex);
                }
            },
            theme: 'bootstrap'
        });

    });
	
	checkbox_select_all();
});


function form_select_images(obj){
	var d=obj.data();
	$(d.tag).html(obj.html());
	$(d.field).val(d.value);
}

var ACTION;


