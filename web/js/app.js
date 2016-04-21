WebFontConfig = {
    google: {families: ['Open Sans:400,400italic,700:latin']}
};
(function () {
    var wf = document.createElement('script');
    wf.src = 'https://ajax.googleapis.com/ajax/libs/webfont/1/webfont.js';
    wf.type = 'text/javascript';
    wf.async = 'true';
    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(wf, s);
})();


$(function () {
    $('input[name="q"]').autocomplete({
        source: '/autocomplete',
        minLength: 2
    });

    $('.citation-source_link').click(function (e) {
        e.preventDefault();
        var that = $(this);

        $.get(that.attr('href'), function(data) {
            $('.modal-body').html(data);
            $('.modal-title').text(that.text())
            $('.modal').modal('toggle');
        })

    });
});
