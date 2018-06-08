var RandBG = function () {
	var pic=new Array();
		pic[0]='/Apps/Admin/View/default/Public/images/login-bg/1.jpg';
		pic[1]='/Apps/Admin/View/default/Public/images/login-bg/2.jpg';
		pic[2]='/Apps/Admin/View/default/Public/images/login-bg/3.jpg';
		pic[3]='/Apps/Admin/View/default/Public/images/login-bg/4.jpg';
		pic[4]='/Apps/Admin/View/default/Public/images/login-bg/5.jpg';
		//alert(randomSort(pic));
	var rand_pic=randomSort(pic);

    return {
        //main function to initiate the module
        init: function () {

            $.backstretch(rand_pic, {
    		          fade: 1000,
    		          duration: 10000
    		    });

        }

    };

}();

RandBG.init();

