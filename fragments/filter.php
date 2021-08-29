<div class="panel panel-default">
    <div class="panel-heading"><?php echo $this->i18n('statistics_filter_date') ?></div>
    <div class="panel-body">
        <form class="form-inline" method="GET">
            <input type="hidden" value="<?php echo $this->current_backend_page ?>" name="page">
            <div class="form-group" style="margin-right: 1rem;">
                <label for="date_start"><?php echo $this->i18n('statistics_startdate') ?></label>
                <input style="line-height: normal;" type="date" value="<?php echo $this->date_start->format('Y-m-d') ?>" class="form-control" name="date_start">
            </div>
            <div class="form-group" style="margin-right: 1rem;">
                <label for="date_end"><?php echo $this->i18n('statistics_enddate') ?></label>
                <input style="line-height: normal;" value="<?php echo $this->date_end->format('Y-m-d') ?>" type="date" class="form-control" name="date_end">
            </div>
            <button type="submit" class="btn btn-default"><?php echo $this->i18n('statistics_filter') ?></button>
        </form>
    </div>
</div>