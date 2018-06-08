






function layerMsg(msg, param = null, callback){
	var set = {
    	time:1500,
    	offset:'300px',
    	skin:'my-layer-error',
	};
	if(param == null){
		layer.msg(msg, set, function(){
			if(callback) callback();
		});
	}else{
		layer.msg(msg, Object.assign(set, param), function(){
			if(callback) callback();
		});
	}
}


































