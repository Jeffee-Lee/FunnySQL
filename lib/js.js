/* Common Part Start */
$(".head > a").click(function () {
    if ($(this).nextAll().length !== 0) {
        //不是最右边的关闭
        $('a.active').removeClass('active');
        $(this).toggleClass('active');
    } else {
        $('#msg').toggle();
        $('#block').toggle();
        $('.msg-body button:first-child').click(function () {
            $.cookie('funnysql', '', {expires: -10, path: "<?php echo $path;?>"});
            window.location.replace('<?php echo $domain . $path;?>index');
        });
    }
});
$('.msg-head a').click(function () {
    $('#msg').hide();
    $('#block').hide();
});
$('.msg-body button:last-child').click(function () {
    $('#msg').hide();
    $('#block').hide();
});
$('#msg').draggable();
/* Common Part End */