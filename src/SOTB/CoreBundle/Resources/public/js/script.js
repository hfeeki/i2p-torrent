$(document).ready(function () {
    $(document).on('click', '.loading', function () {
        var btn = $(this);
        btn.button('loading');
        setTimeout(function () {
            btn.button('reset')
        }, 5000);
    });
});