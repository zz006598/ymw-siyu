/**
 +-------------------------------------------------------------------
 * jQuery FontScroll - 文字行向上滚动插件 - http://java2.sinaapp.com
 +-------------------------------------------------------------------
 * @version    1.0.0 beta
 * @since      2014.06.12
 * @author     kongzhim <kongzhim@163.com> <http://java2.sinaapp.com>
 * @github     http://git.oschina.net/kzm/FontScroll
 +-------------------------------------------------------------------
 */

(function($) {
    $.fn.FontScroll = function(options) {
        var d = { time: 3000, s: 'fontColor', num: 1 }
        var o = $.extend(d, options);


        this.children('ul').addClass('line');
        var _con = $('.line').eq(0);
        var _conH = _con.height(); //滚动总高度
        var _conChildH = _con.children().eq(0).height(); //一次滚动高度
        var _temp = _conChildH; //临时变量
        var _time = d.time; //滚动间隔
        var _s = d.s; //滚动间隔


        _con.clone().insertAfter(_con); //初始化克隆

        //样式控制
        var num = d.num;
        var _p = this.find('li');
        var allNum = _p.length;

        _p.eq(num).addClass(_s);


        var timeID = setInterval(Up, _time);
        this.hover(function() { clearInterval(timeID) }, function() { timeID = setInterval(Up, _time); });

        function Up() {
            _con.animate({ marginTop: '-' + _conChildH });
            //样式控制
            _p.removeClass(_s);
            num += 1;
            _p.eq(num).addClass(_s);

            if (_conH == _conChildH) {
                _con.animate({ marginTop: '-' + _conChildH }, "normal", over);
            } else {
                _conChildH += _temp;
            }
        }

        function over() {
            _con.attr("style", 'margin-top:0');
            _conChildH = _temp;
            num = 1;
            _p.removeClass(_s);
            _p.eq(num).addClass(_s);
        }
    }
})(jQuery);


$(function() {
    $('.announce-wrap').FontScroll({ time: 5000, num: 1 });
});

/**
 +-------------------------------------------------------------------
 * jQuery 
 +-------------------------------------------------------------------
 * @version    1.0.0 banner
 * @since      2020.01.12
 * @author     素材虎 www.sucaihu.com
 * @github     https://www.sucaihu.com
 +-------------------------------------------------------------------
 */
jQuery(document).ready(function () {
  var mySwiper = new Swiper('.swiper-container', {
    direction: 'horizontal', // 垂直切换选项
    loop: true, // 循环模式选项
    autoplay: true,

    // 如果需要分页器
    pagination: {
      el: '.swiper-pagination',
    },

    // 如果需要前进后退按钮
    navigation: {
      nextEl: '.swiper-button-next',
      prevEl: '.swiper-button-prev',
    },

    // 如果需要滚动条
    scrollbar: {
      el: '.swiper-scrollbar',
    },
  })
})

/**
 +-------------------------------------------------------------------
 * jQuery 
 +-------------------------------------------------------------------
 * @version    1.0.0 tab
 * @since      2020.01.12
 * @author     素材虎 www.sucaihu.com
 * @github     https://www.sucaihu.com
 +-------------------------------------------------------------------
 */
$(function() {
    $.fn.FengTab = function(b) {
        b = $.extend({
            titCell: "",
            mainCell: "",
            defaultIndex: 0,
            trigger: "click",
            titOnClassName: "on",
            showtime: 200
        }, b);
        var c = $(this)
          , tabLi = c.find(b.titCell).children()
          , conDiv = c.find(b.mainCell).children();
        conDiv.each(function() {
            var a = $(this)
              , index = a.index();
            a.addClass("FengTabCon_" + index);
            if (index == b.defaultIndex) {
                a.show()
            } else {
                a.hide()
            }
        });
        tabLi.each(function() {
            var a = $(this)
              , index = a.index();
            if (index == b.defaultIndex) {
                a.addClass(b.titOnClassName)
            }
            ;a.on(b.trigger, function() {
                a.addClass(b.titOnClassName).siblings().removeClass(b.titOnClassName);
                boxItem = c.find(b.mainCell).children(".FengTabCon_" + index);
                boxItem.stop();
                boxItem.fadeIn(b.showtime).siblings().hide()
            })
        })
    }

	$(".article-content").FengTab({
		titCell:".tabtst", 	//选项卡控制盒子
		mainCell:".container", 	//选项卡内容盒子
		defaultIndex:"0", 	//默认显示第几个选项卡，第一个是0，第二个是1，以此类推
		trigger:"click", 	//切换方式，click 为点击，mouseover 为移动切换
		titOnClassName:"on", 	//选中选项卡样式
		showtime: 0		//内容切换时间，一般写200即可（单位是毫秒）
	});
});
/***
	@name:触屏事件
	@param {string} element dom元素
			 {function} fn 事件触发函数
***/
function v_on(obj,ev,fn) {
	if(obj.attachEvent) {
		obj.attachEvent("on" + ev,fn);
	} else {
		obj.addEventListener(ev,fn,false);
	}
}
var touchEvent={
	/*单次触摸事件*/
	tap:function(element,fn){
		var startTx, startTy;
		v_on(element,'touchstart',function(e){
			var touches = e.touches[0];
			startTx = touches.clientX;
			startTy = touches.clientY;
		}, false );

		v_on(element,'touchend',function(e){
			var touches = e.changedTouches[0],
			endTx = touches.clientX,
			endTy = touches.clientY;
			// 在部分设备上 touch 事件比较灵敏，导致按下和松开手指时的事件坐标会出现一点点变化
			if( Math.abs(startTx - endTx) < 6 && Math.abs(startTy - endTy) < 6 ){
			fn();
			}
		}, false );
	},

	/*两次触摸事件*/
	doubleTap:function(element,fn){
		var isTouchEnd = false,
		lastTime = 0,
		lastTx = null,
		lastTy = null,
		firstTouchEnd = true,
		body = document.body,
		dTapTimer, startTx, startTy, startTime;
		v_on(element, 'touchstart', function(e){
			if( dTapTimer ){
			clearTimeout( dTapTimer );
			dTapTimer = null;
			}
			var touches = e.touches[0];
			startTx = touches.clientX;
			startTy = touches.clientY;
		}, false );
		v_on(element, 'touchend',function(e){
			var touches = e.changedTouches[0],
			endTx = touches.clientX,
			endTy = touches.clientY,
			now = Date.now(),
			duration = now - lastTime;
			// 首先要确保能触发单次的 tap 事件
			if( Math.abs(startTx - endTx) < 6 && Math.abs(startTx - endTx) < 6 ){
			// 两次 tap 的间隔确保在 500 毫秒以内
			if(duration < 301 ){
				// 本次的 tap 位置和上一次的 tap 的位置允许一定范围内的误差
				if( lastTx !== null &&
				Math.abs(lastTx - endTx) < 45 &&
				Math.abs(lastTy - endTy) < 45 ){
					firstTouchEnd = true;
					lastTx = lastTy = null;
					fn();
				}
				}
				else{
				lastTx = endTx;
				lastTy = endTy;
				}
			}
			else{
				firstTouchEnd = true;
				lastTx = lastTy = null;
			}
			lastTime = now;
			}, false );
			// 在 iOS 的 safari 上手指敲击屏幕的速度过快，
			// 有一定的几率会导致第二次不会响应 touchstart 和 touchend 事件
			// 同时手指长时间的touch不会触发click
			if(~navigator.userAgent.toLowerCase().indexOf('iphone os')){
			v_on(body, 'touchstart', function(e){
				startTime = Date.now();
			}, true );
			v_on(body, 'touchend', function(e){
				var noLongTap = Date.now() - startTime < 501;
				if(firstTouchEnd ){
				firstTouchEnd = false;
				if( noLongTap && e.target === element ){
					dTapTimer = setTimeout(function(){
					firstTouchEnd = true;
					lastTx = lastTy = null;
					fn();
					},400);
				}
				}
				else{
				firstTouchEnd = true;
				}
			}, true );
			// iOS 上手指多次敲击屏幕时的速度过快不会触发 click 事件
			v_on(element, 'click', function( e ){
				if(dTapTimer ){
				clearTimeout( dTapTimer );
				dTapTimer = null;
				firstTouchEnd = true;
				}
			}, false );
		}
	},

	/*长按事件*/
	longTap:function(element,fn){
		var startTx, startTy, lTapTimer;
		v_on(element, 'touchstart', function( e ){
			if( lTapTimer ){
			clearTimeout( lTapTimer );
			lTapTimer = null;
			}
			var touches = e.touches[0];
			startTx = touches.clientX;
			startTy = touches.clientY;
			lTapTimer = setTimeout(function(){
			fn();
			}, 1000 );
			//e.preventDefault();
		}, false );
		v_on(element, 'touchmove', function( e ){
			var touches = e.touches[0],
			endTx = touches.clientX,
			endTy = touches.clientY;
			if( lTapTimer && (Math.abs(endTx - startTx) > 5 || Math.abs(endTy - startTy) > 5) ){
			clearTimeout( lTapTimer );
			lTapTimer = null;
			}
		}, false );
		v_on(element, 'touchend', function( e ){
			if( lTapTimer ){
			clearTimeout( lTapTimer );
			lTapTimer = null;
			}
		}, false );
	},

	/*滑屏事件*/
	swipe:function(element,fn){
		var isTouchMove, startTx, startTy;
		v_on(element, 'touchstart', function( e ){
			var touches = e.touches[0];
			startTx = touches.clientX;
			startTy = touches.clientY;
			isTouchMove = false;
		}, false );
		v_on(element, 'touchmove', function( e ){
			isTouchMove = true;
			e.preventDefault();
		}, false );
		v_on(element, 'touchend', function( e ){
			if( !isTouchMove ){
			return;
			}
			var touches = e.changedTouches[0],
			endTx = touches.clientX,
			endTy = touches.clientY,
			distanceX = startTx - endTx
			distanceY = startTy - endTy,
			isSwipe = false;
			if( Math.abs(distanceX)>20||Math.abs(distanceY)>20 ){
			fn();
			}
		}, false );
	},

	/*向上滑动事件*/
	swipeUp:function(element,fn){
		var isTouchMove, startTx, startTy;
		v_on(element, 'touchstart', function( e ){
			var touches = e.touches[0];
			startTx = touches.clientX;
			startTy = touches.clientY;
			isTouchMove = false;
		}, false );
		v_on(element, 'touchmove', function( e ){
			isTouchMove = true;
			e.preventDefault();
		}, false );
		v_on(element, 'touchend', function( e ){
			if( !isTouchMove ){
			return;
			}
			var touches = e.changedTouches[0],
			endTx = touches.clientX,
			endTy = touches.clientY,
			distanceX = startTx - endTx
			distanceY = startTy - endTy,
			isSwipe = false;
			if( Math.abs(distanceX) < Math.abs(distanceY) ){
				if( distanceY > 20 ){
					fn();
					isSwipe = true;
				}
			}
		}, false );
	},

	/*向下滑动事件*/
	swipeDown:function(element,fn){
		var isTouchMove, startTx, startTy;
		v_on(element, 'touchstart', function( e ){
			var touches = e.touches[0];
			startTx = touches.clientX;
			startTy = touches.clientY;
			isTouchMove = false;
		}, false );
		v_on(element, 'touchmove', function( e ){
			isTouchMove = true;
			//e.preventDefault();
		}, false );
		v_on(element, 'touchend', function( e ){
			if( !isTouchMove ){
			return;
			}
			var touches = e.changedTouches[0],
			endTx = touches.clientX,
			endTy = touches.clientY,
			distanceX = startTx - endTx
			distanceY = startTy - endTy,
			isSwipe = false;
			if( Math.abs(distanceX) < Math.abs(distanceY) ){
				if( distanceY < -20  ){
					fn();
					isSwipe = true;
				}
			}
		}, false );
	},

	/*向左滑动事件*/
	swipeLeft:function(element,fn){
		var isTouchMove, startTx, startTy;
		v_on(element, 'touchstart', function( e ){
			var touches = e.touches[0];
			startTx = touches.clientX;
			startTy = touches.clientY;
			isTouchMove = false;
		}, false );
		v_on(element, 'touchmove', function( e ){
			isTouchMove = true;
			e.preventDefault();
		}, false );
		v_on(element, 'touchend', function( e ){
			if( !isTouchMove ){
			return;
			}
			var touches = e.changedTouches[0],
			endTx = touches.clientX,
			endTy = touches.clientY,
			distanceX = startTx - endTx
			distanceY = startTy - endTy,
			isSwipe = false;
			if( Math.abs(distanceX) >= Math.abs(distanceY) ){
				if( distanceX > 20  ){
					fn();
					isSwipe = true;
				}
			}
		}, false );
	},

	/*向右滑动事件*/
	swipeRight:function(element,fn){
		var isTouchMove, startTx, startTy;
		v_on(element, 'touchstart', function( e ){
			var touches = e.touches[0];
			startTx = touches.clientX;
			startTy = touches.clientY;
			isTouchMove = false;
		}, false );
		v_on(element, 'touchmove', function( e ){
			isTouchMove = true;
			e.preventDefault();
		}, false );
		v_on(element, 'touchend', function( e ){
			if( !isTouchMove ){
			return;
			}
			var touches = e.changedTouches[0],
			endTx = touches.clientX,
			endTy = touches.clientY,
			distanceX = startTx - endTx
			distanceY = startTy - endTy,
			isSwipe = false;
			if( Math.abs(distanceX) >= Math.abs(distanceY) ){
				if( distanceX < -20  ){
					fn();
					isSwipe = true;
				}
			}
		}, false );
	}
}

jQuery.fn.extend({
	tap:function (fn) {
		return touchEvent.tap(jQuery(this)[0],fn);
	},
	doubleTap:function (fn) {
		return touchEvent.doubleTap(jQuery(this)[0],fn);
	},
	longTap:function (fn) {
		return touchEvent.longTap(jQuery(this)[0],fn);
	},
	swipe:function (fn) {
		return touchEvent.swipe(jQuery(this)[0],fn);
	},
	swipeLeft:function (fn) {
		return touchEvent.swipeLeft(jQuery(this)[0],fn);
	},
	swipeRight:function (fn) {
		return touchEvent.swipeRight(jQuery(this)[0],fn);
	},
	swipeUp:function (fn) {
		return touchEvent.swipeUp(jQuery(this)[0],fn);
	},
	swipeDown:function (fn) {
		return touchEvent.swipeDown(jQuery(this)[0],fn);
	}
});

/***
	@name:点击滚动事件
			 {function} fn 事件触发函数
***/
!function(l) {
    l(".scroll-h").each(function() {
        var t = l(this),
        e = t.children("ul");
        if (! (e.length < 2)) {
            var n = 0,
            r = e.length,
            o = t.parent().siblings(".hf-widget-title").children(".pages"); !
            function c() {
                0 < o.length || (t.parent().siblings(".hf-widget-title").append('<div class="pages"><i class="prev"> <i class="icon-left"></i> </i><i class="next"> <i class="icon-right"></i> </i></div>'), o = t.parent().siblings(".hf-widget-title").children(".pages"))
            } ();
            var i = o.children(".prev"),
            a = o.children(".next");
            i.on("click",
            function() { !
                function t() {--n < 0 && (n = r - 1)
                } (),
                s()
            }),
            a.on("click",
            function() { !
                function t() {
                    r <= ++n && (n = 0)
                } (),
                s()
            }),
            touchEvent.swipeLeft(this,
            function() {
                a.trigger("click")
            }),
            touchEvent.swipeRight(this,
            function() {
                i.trigger("click")
            })
        }
        function s() {
            e.addClass("holdon"),
            e.removeClass("holdon-prev"),
            e.eq(n).removeClass("holdon"),
            e.eq(n - 1).addClass("holdon-prev")
        }
    })
} (jQuery);
