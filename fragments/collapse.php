<?php

$rnd = rand(0, 100);

?>

<div class="text-center" style="margin-top: 22px;">
    <a data-toggle="collapse" data-target="#collapseTable<?php echo $rnd ?>">
        <?php echo $this->i18n('statistics_toggle_collapse_table') ?>
    </a>
</div>

<div class="collapse" id="collapseTable<?php echo $rnd ?>">
    <div class="well">
        <?php echo $this->content ?>
    </div>
</div>