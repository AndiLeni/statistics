<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-default">
            <header class="panel-heading">
                <div class="panel-title" style="display:flex; align-items:center;">
                    <b><?php echo $this->title; ?></b>
                    <?php if (isset($this->note)) : ?>
                        <button type="button" class="btn btn-link" data-toggle="modal" data-target="#<?= $this->modalid; ?>">
                            <i class="rex-icon fa-fw rex-icon-info"></i>
                        </button>
                    <?php endif ?>
                </div>
            </header>
            <div class="panel-body">
                <div class="row">
                    <div class="col-sm-12 col-lg-6">
                        <?php echo $this->chart; ?>
                    </div>
                    <div class="col-sm-12 col-lg-6">
                        <?php echo $this->table; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (isset($this->note)) : ?>

    <div class="modal fade" id="<?= $this->modalid; ?>">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel"><b><?= $this->title; ?></b></h4>
                </div>
                <div class="modal-body">
                    <?= $this->note; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Schließen</button>
                </div>
            </div>
        </div>
    </div>

<?php endif ?>