<div class="row">
    <div class="col-12 col-md-4">
        <div class="panel panel-default">
            <header class="panel-heading">
                <div class="panel-title">
                    <b>
                        <?php
                        echo $this->date_start->format('d.m.Y');
                        echo ' - ';
                        echo $this->date_end->format('d.m.Y')
                        ?>
                    </b>
                </div>
            </header>
            <div class="panel-body">
                <p class="h3 statistics_my-0">Seitenaufrufe : <b><?php echo $this->filtered_visits; ?></b></p>
                <hr class="statistics_hr-margin-small">
                <p class="h3 statistics_my-0">Besucher : <b><?php echo $this->filtered_visitors; ?></b></p>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="panel panel-default">
            <header class="panel-heading">
                <div class="panel-title"><b>Heute</b></div>
            </header>
            <div class="panel-body">
                <p class="h3 statistics_my-0">Seitenaufrufe : <b><?php echo $this->today_visits; ?></b></p>
                <hr class="statistics_hr-margin-small">
                <p class="h3 statistics_my-0">Besucher : <b><?php echo $this->today_visitors; ?></b></p>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="panel panel-default">
            <header class="panel-heading">
                <div class="panel-title"><b>Insgesamt</b></div>
            </header>
            <div class="panel-body">
                <p class="h3 statistics_my-0">Seitenaufrufe : <b><?php echo $this->total_visits; ?></b></p>
                <hr class="statistics_hr-margin-small">
                <p class="h3 statistics_my-0">Besucher : <b><?php echo $this->total_visitors; ?></b></p>
            </div>
        </div>
    </div>
</div>