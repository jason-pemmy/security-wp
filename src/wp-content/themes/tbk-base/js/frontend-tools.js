(function($) {
    function fetool(e) {
        var evtobj = window.event? event : e
        if (evtobj.keyCode == 90 && evtobj.ctrlKey) {
            $('.front-end-tools').toggleClass(" show");
        }
    }



    $(document).ready(function() {

        function wah() {
            var bodywidth = $('body').width();
            var bodyheight = $('body').height();
            $('.screen-text').text(bodywidth +'px ' +'/ '+ bodyheight +'px ');
        }

        $(window).resize(function() {
            wah();
        });

        wah();

    });

    document.onkeydown = fetool;
})(jQuery);
