<section class="rex-page-section">


    <div class="panel panel-default">

        <header class="panel-heading">
            <div class="panel-title"><b>Aufrufe:</b></div>
        </header>

        <div class="panel-body">

            <div class="rex-page-nav">

                <!-- Nav tabs -->
                <ul class="nav nav-pills nav-justified" role="tablist" style="border: 2px solid #4b9ad9;">
                    <li role="presentation" class="active"><a href="#home" aria-controls="home" role="tab" data-toggle="tab">Täglich</a></li>
                    <li role="presentation"><a href="#profile" aria-controls="profile" role="tab" data-toggle="tab">Monatlich</a></li>
                    <li role="presentation"><a href="#messages" aria-controls="messages" role="tab" data-toggle="tab">Jährlich</a></li>
                </ul>

                <hr>

                <!-- Tab panes -->
                <div class="tab-content" style="margin-top: 20px;">
                    <div role="tabpanel" class="tab-pane active" id="home">
                        <?php echo $this->daily; ?>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="profile">
                        <?php echo $this->monthly; ?>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="messages">
                        <?php echo $this->yearly; ?>
                    </div>
                </div>

            </div>

        </div>
    </div>


</section>