 // 浏览器版本检测
! function() {
    var ua,
        match;
    ua = window.navigator.userAgent;
    match = /;\s*MSIE (\d+).*?;/.exec(ua);
    if (match && +match[1] <= 9) {
        window.location.replace(window._config.global.static_url+'html/browser.html')
    }
}();