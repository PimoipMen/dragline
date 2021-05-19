function  inputFocus (e) {
    var winobj = $(window),
        scope = this,
        agent = navigator.userAgent.toLowerCase();
    setTimeout(function () {
        if (agent.indexOf('safari') != -1 && agent.indexOf('mqqbrowser') == -1
            && agent.indexOf('coast') == -1 && agent.indexOf('android') == -1
            && agent.indexOf('linux') == -1 && agent.indexOf('firefox') == -1) {//safra浏览器
            window.scrollTo(0, 1000000);//先滚动到最底部，再用scrollY得到当前的值，必须延迟 否则拿到的就是1000000
            setTimeout(function(){
                window.scrollTo(0, window.scrollY - 45);//45像素 所有浏览器都是这么高
            }, 50)
        } else {//其他浏览器
            window.scrollTo(0, 1000000);
            // window.scrollTo(0, ++this.scrollNum);
        }
    }, 200);
}
(function(doc, win) {
    var docEl = doc.documentElement,
        resizeEvt = 'orientationchange' in window ? 'orientationchange' : 'resize',
        recalc = function() {
            var clientWidth = docEl.clientWidth;
            if(!clientWidth) return;
            if(clientWidth >= 750) {
                docEl.style.fontSize = '100px';
            } else {
                docEl.style.fontSize = 100 * (clientWidth / 750) + 'px';
            }
        };

    if(!doc.addEventListener) return;
    win.addEventListener(resizeEvt, recalc, false);
    doc.addEventListener('DOMContentLoaded', recalc, false);
})(document, window);