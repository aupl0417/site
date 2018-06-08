
$(window).scroll(function(e){
	if( $(window).scrollTop() > 600){
		$('#left_ladder').fadeIn();
	}else{
		$('#left_ladder').fadeOut();
	};

	for(i=0; i< $("#left_ladder a").length-1; i++){
		$("#left_ladder a").eq(i).click(function(){
			var num = $('.floor-public').eq( $(this).index() ).offset().top - 20;
			$('html,body').stop().animate( { scrollTop : num },300 );
		})
	}
	

	a = -1;
	$("#left_ladder a").each(function(){
		if( $(this).index() != $(".floor-public").length ){
			var num = $('.floor-public').eq( $(this).index() ).offset().top;
			if( num - 400 < $(window).scrollTop()){
				$("#left_ladder a").find('div').css('display','none');
				$(this).find('div').css('display','block');
				a = $(this).index();
			}else if( $(window).scrollTop() < 1300){
				$("#left_ladder a").find('div').css('display','none');
			}
		}
	})

	$("#left_ladder a").mouseover(function(){
		$(this).find('div').css('display','block')
	})

	$("#left_ladder a").mouseout(function(){
		if( $(this).index() == a ){
			$(this).find('div').css('display','block')
		}else{
			$(this).find('div').css('display','none')
		}
	})

});

$(function(){
	var n = true;
	$('#cart_hide').click(function(){
		console.log(n)
		if(n){
			$('#cart_right').stop().animate( { right : 0 },150 );
			$(this).addClass('cart_in')
			n = false;	
		}else{
			$('#cart_right').stop().animate( { right : -250 },150 );
			$(this).removeClass('cart_in')
			n = true;
		}
	})
	$("#cart_right").mouseover(function(event){
         //return false;
         event.stopPropagation();
    });
    $("body").mouseover(function(event){
    	$('#cart_right').stop().animate( { right : -250 },150 );
    	$('#cart_hide').removeClass('cart_in')
    	n = true;
    });
})
	




