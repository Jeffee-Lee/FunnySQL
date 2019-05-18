
let common = new Vue({
    el: "#common",
    data: {
        isMsgShow: false,
        isShowError: false,
        isShowSuccess: false,
        message: null,
        isShowLoader: false,
        isShowClose: false
    },
    methods: {
        showError: function (error) {
            common.message = error;
            common.isShowError = true;
            common.isMsgShow = true;
            setTimeout(function () {
                common.hideMsg();
            }, 1500)
        },
        showSuccess: function (msg) {
            common.message = msg;
            common.isShowSuccess = true;
            common.isMsgShow = true;
            setTimeout(function () {
                common.hideMsg();
            }, 1500);
        },
        hideMsg: function () {
            common.isMsgShow = false;
            common.isShowError = false;
            common.isShowSuccess = false;
            common.message = null;
        },
        showClose: function () {
            common.isShowClose = true;
        },
        hideClose: function () {
            common.isShowClose = false;
        },
        showLoader: function () {
            common.isShowLoader = true;
        },
        hideLoader: function () {
            common.isShowLoader = false;
        },
        logout: function () {
            let params = new URLSearchParams();
            params.append("type", "2");
            axios.post("./lib/Processing.php", params.toString())
                .then(function (response) {
                    window.location.href = './';
                });
        }
    },
    mounted: function () {
        this.$nextTick(function () {
            // $("#close").draggable();
            let close = document.getElementById("close");
            let dragging, tLeft, tTop;
            document.addEventListener("mousedown", function (e) {

                if (e.target.parentNode === close) {
                    let move = close.getBoundingClientRect();
                    dragging = true;
                    tLeft = e.clientX - move.left;
                    tTop = e.clientY - move.top;
                }
            });
            document.addEventListener("mouseup", function (e) {
                if (e.target.parentNode === close) {
                    dragging = false;
                }
            });
            document.addEventListener("mousemove", function (e) {
                if (dragging) {
                    let moveX = e.clientX - tLeft,
                        moveY = e.clientY - tTop;
                    close.style.left = moveX + 'px';
                    close.style.top = moveY + 'px';
                }
            });
        })
    }
});
