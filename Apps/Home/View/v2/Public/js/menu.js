
window.onload = window.onresize = window.onscroll=function(){
	var oFloor = document.getElementById('floor-lefticon');	
	var scrollTop = document.documentElement.scrollTop||document.body.scrollTop;	
	if((750-scrollTop)<200){
		oFloor.style.display='block';
	}else{
		oFloor.style.display='none';
	}	
	function oInitial(numberr){
		var oF_i = document.getElementById(numberr+'f');
		if((oF_i.offsetTop+670-scrollTop)<200){
			var oLi = oFloor.getElementsByTagName('a');
			for(i=0; i<oLi.length; i++){
				oLi[i].getElementsByTagName('img')[0].style.display = 'none';
			}
			oLi[numberr-1].getElementsByTagName('img')[0].style.display = 'block';
			for(i=0; i<oLi.length; i++){
				oLi[i].index=i;
				oLi[i].onmouseover = oLi[i].onclock = function(){
					this.getElementsByTagName('img')[0].style.display = 'block';
				}
				oLi[i].onmouseout = function(){
					if(this.index == numberr-1){
						this.getElementsByTagName('img')[0].style.display = 'block';
					}else{
						this.getElementsByTagName('img')[0].style.display = 'none';
					}
				}
			} 
		} 
	}	
	oInitial("1");
	oInitial("2");
	oInitial("3");
	oInitial("4");
	oInitial("5");
	oInitial("6");
	oInitial("7");
	
	
	var oSy_R = document.getElementsByClassName('sy_R')[0];
	var oTitle_logo = document.getElementById('title_logo');
	var oLock = oTitle_logo.getElementsByClassName('lock');
	var oR_tent = document.getElementById('R_tent');
	var oFunct = oR_tent.getElementsByClassName('funct');

	oSy_R.onmouseover = function(ev){	//阻止冒泡
		var oEvent = ev||event;
		oEvent.cancelBubble = true;
	}

	document.onmouseover = function(){	//右面菜单缩回
		oSy_R.style.right='-235px';
	}

	for(var i=0; i<oLock.length; i++){
		oLock[i].index = i;
		oLock[i].onclick = function(){			
			for(i=0; i<oFunct.length; i++){				
				oFunct[i].style.display = 'none';
			}
			oFunct[this.index].style.display = 'block';
			oSy_R.style.right='0px';
		}
	}


	
	var oSy_top = document.getElementById('sy_top_sub');//首页头部搜索
	scrollTop>700?oSy_top.style.display='block':oSy_top.style.display='none';
}