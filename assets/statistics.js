$(document).on("rex:ready", function (event, container) {
    statistics_datefilter_start = document.getElementById("statistics_datefilter_start");
    statistics_df_lsd = document.getElementById("statistics_df_lsd");
    statistics_df_ltd = document.getElementById("statistics_df_ltd");
    statistics_df_ty = document.getElementById("statistics_df_ty");
    statistics_df_wt = document.getElementById("statistics_df_wt");
    statistics_df_form = document.getElementById("statistics_df_form");

    // if input field exists add event listeners
    if (statistics_datefilter_start != null) {
        statistics_df_lsd.addEventListener("click", function () {
            last_seven_days = get_past_date(7);
            statistics_datefilter_start.value = last_seven_days;
            statistics_df_form.submit();
        });
        statistics_df_ltd.addEventListener("click", function () {
            last_thirty_days = get_past_date(30);
            statistics_datefilter_start.value = last_thirty_days;
            statistics_df_form.submit();
        });
        statistics_df_ty.addEventListener("click", function () {
            last_year = get_past_date(365);
            statistics_datefilter_start.value = last_year;
            statistics_df_form.submit();
        });
        statistics_df_wt.addEventListener("click", function () {
            whole_time = statistics_df_wt.getAttribute("data-start");
            statistics_datefilter_start.value = whole_time;
            statistics_df_form.submit();
        });
    }

    function get_past_date(minusDays) {
        var date = new Date();
        date.setDate(date.getDate() - minusDays);
        day = ("0" + date.getDate()).slice(-2);
        month = ("0" + (date.getMonth() + 1)).slice(-2);
        year = date.getFullYear();
        str = year + "-" + month + "-" + day;

        return str;
    }
});
