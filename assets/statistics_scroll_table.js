// scroll to table on datatable pagination click
$(document).on('rex:ready', function () {
    $('.table').on('page.dt', function () {
        $('html, body').animate({
            scrollTop: $(this).closest('.dataTables_wrapper').offset().top
        }, 'slow');
    });
})