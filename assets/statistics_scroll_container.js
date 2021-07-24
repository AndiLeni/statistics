// scroll to panel on datatable pagination click
$(document).on('rex:ready', function () {
    $('.table').on('page.dt', function () {
        $('html, body').animate({
            scrollTop: $(this).closest('.rex-page-section').offset().top
        }, 'slow');
    });
})