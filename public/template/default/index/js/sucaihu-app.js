  //QQ login
jQuery(document).on("click", ".qqbutton",
    function(event) {
    	event.preventDefault();
        window.location.href = $(this).attr('href');
    	return true;
});

//weibo login
jQuery(document).on("click", ".wbbutton",
    function(event) {
    	event.preventDefault();
        window.location.href = $(this).attr('href');
    	return true;
});

jQuery(document).on("click", ".loginbutton",
    function() {
    open_signup_popup();
    return true; 
});
 

var windowTop=0;//初始话可视区域距离页面顶端的距离
$(window).scroll(function() {
    var scrolls = $(this).scrollTop();//获取当前可视区域距离页面顶端的距离
    if(scrolls>=windowTop){//当scrolls>windowTop时，表示页面在向下滑动
        //$(".wic_slogin").hide()

            $('.wic_slogin').css('bottom','-110px');
            $('.wic_slogin').css('opacity','0');
            
            //$('.site-header').css('top','-80px');
            //$('.site-header').css('opacity','0');
            
        windowTop=scrolls;
    }else{
        //$(".wic_slogin").show()
            $('.wic_slogin').css('bottom','0px');
            $('.wic_slogin').css('opacity','1');

	    	//$('.site-header').css('top','0px');
            //$('.site-header').css('opacity','1');
	    	
        windowTop=scrolls;
    }
});
