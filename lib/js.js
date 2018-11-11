$(document).ready(function () {
    /* Common Part Start*/
    $("#msg-close").click(function () {
        $("#msg").removeClass("msgShow").hide().find('#msg-body').text('');
    });
    $("#exit").click(function () {
        $("#close, #fullScreen").show();
    });
    $('.close-head a, .close-body button:last-child').click(function () {
        $("#close, #fullScreen").hide();
    });
    $("#close").draggable();
    /* Common Part End */
});

function showLoader() {
    $('#loader').css("display","flex")
}
function hideLoader() {
    $('#loader').css("display",'none');
}
function showMsg(Message, type) {
    let color = 'red';
    if(type === undefined || type === 'error')
        color = "red";
    else if (type === 'success')
        color = "#00ff2b";
    if(Message != null) {
        $("#msg").css('background',color).addClass("msgShow").find('#msg-body').text(Message).parent('#msg').show();
        setTimeout(function(){
            $("#msg").removeClass("msgShow").find('#msg-body').text('').parent('#msg').hide();
        }, 3333)
    }
}
