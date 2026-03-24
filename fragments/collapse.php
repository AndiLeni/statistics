<?php

$rnd = rand(0, 100);
$lazyBlockId = (string) $this->getVar('lazy_block_id', '');
$dateStart = (string) $this->getVar('date_start', '');
$dateEnd = (string) $this->getVar('date_end', '');

?>

<div class="text-center" style="margin-top: 22px;">
    <a data-toggle="collapse" data-target="#collapseTable<?php echo $rnd ?>">
        <?php echo $this->i18n('statistics_toggle_collapse_table') ?>
    </a>
</div>

<div class="collapse" id="collapseTable<?php echo $rnd ?>">
    <div class="well">
        <?php if ('' !== $lazyBlockId) { ?>
            <div
                data-statistics-lazy-collapse
                data-block-id="<?php echo htmlspecialchars($lazyBlockId, ENT_QUOTES) ?>"
                data-date-start="<?php echo htmlspecialchars($dateStart, ENT_QUOTES) ?>"
                data-date-end="<?php echo htmlspecialchars($dateEnd, ENT_QUOTES) ?>"
                data-state="idle"
            ></div>
        <?php } else { ?>
            <?php echo $this->content ?>
        <?php } ?>
    </div>
</div>