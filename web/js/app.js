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
        var citationBlock = $(this).parent('.citation').children('.source-detail-info');
        citationBlock.toggle();

        $.get($(this).attr('href'), function (data) {
            citationBlock.html(data);
        });


    });
});
