$(function() {
    var close = $('#right_ad .close');
    var date = new Date().getDate();
    var box = $('#right_ad');
    var link = $('#right_ad .link');
    var kubao = $('#right_ad .kubao');
    var uid = "";
    var norefush = false;
    function kubaoInBox(t) {
        //if (localStorage.getItem('center_ad_1' + date) || !uid) {
        if (t) {
            kubao.hide();
            box.addClass('right-ad-active').show();
            link.show();
        } else {
            link.hide();
            box.removeClass('right-ad-active').show();
            kubao.show();
        }
        //}
    }

    if (!localStorage.getItem('right_ad' + date)) {
        kubaoInBox(!0);
    } else {
        kubaoInBox(!1);
    }
    kubao.on('mouseenter', function() {
        kubaoInBox(!0);
    });
    link.on('mouseleave', function() {
        if (localStorage.getItem('right_ad' + date) && !norefush) {
            kubaoInBox(!1);
        }
    });
    close.on('click', function(e) {
        e.stopPropagation();
        e.preventDefault();
        box.hide();
        norefush = true;
        localStorage.setItem('right_ad' + date, 1);
    });

    function completeZero(num, length) {
        return (Array(length).join("0") + num).slice(-length);
    }

    var slicer = 9 // 倒计时终点
    var spell = 12 * 60 * 60 * 1000;

    //middleDownTime(slicer);

    function middleDownTime(slicer) {
        var endTime, nowTime, leftTime, h, m, s, endHour;
        var $hour = $('.right-ad-active .hour');
        var $minute = $('.right-ad-active .minute');
        var $second = $('.right-ad-active .second');

        function run() {
            nowTime = new Date();
            endTime = new Date(+new Date + spell);
            endHour = endTime.getHours();
            if (endHour >= 0 && endHour < slicer) { //
                endTime.setHours(slicer + 12);
            } else if (endHour > slicer && endHour < (slicer + 12)) { //
                endTime.setHours(slicer);
            } else if (endHour > (slicer + 12) && endHour < 24) { //
                endTime.setHours(slicer + 12);
            }
            endTime.setMinutes(0);
            endTime.setSeconds(0);
            endTime.setMilliseconds(0);
            leftTime = endTime.getTime() - nowTime.getTime();
            h = Math.floor(leftTime / 1000 / 60 / 60 % 24);
            m = Math.floor(leftTime / 1000 / 60 % 60);
            s = (leftTime / 1000 % 60).toFixed(1);
            $hour.text(completeZero(h, 2));
            $minute.text(completeZero(m, 2));
            $second.text(completeZero(s, 4));
        }

        run();
        startCount = setInterval(run, 100);
    }

    // 倒计时6天 每天更换图片
    var activityEndTime = new Date('2019/12/16 23:59:59');
    var currTime = new Date();
    var curr_date = '' + currTime.getFullYear() + currTime.getMonth() + currTime.getDate();
    var leftDays;

    if (+currTime > +activityEndTime) { // 活动结束
        leftDays = 1;
    } else {
        leftDays = Math.ceil((+activityEndTime - +new Date()) / 1000 / 60 / 60 / 24);
    }

    function countDown() {
        var nowTime = new Date();
        var now_date = '' + nowTime.getFullYear() + nowTime.getMonth() + nowTime.getDate();
        $('#right_ad .link').addClass('day' + leftDays);
        if (curr_date != now_date) {
            leftDays--;
            leftDays = leftDays <= 1 ? 1 : leftDays;
            $('#right_ad .link').addClass('day' + leftDays);
            curr_date = now_date;
        }

    }
});
