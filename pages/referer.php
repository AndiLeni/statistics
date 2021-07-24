<?php

$list = rex_list::factory('SELECT referer, count from ' . rex::getTable('pagestats_referer') . ' ORDER BY count DESC, referer ASC', 500);

$list->setColumnLabel('referer', $this->i18n('statistics_url'));
$list->setColumnLabel('count', $this->i18n('statistics_count'));
$list->addTableAttribute('class', 'table-bordered');


$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('statistics_all_referer'));
$fragment->setVar('body', $list->get(), false);
echo $fragment->parse('core/page/section.php');

?>


<script>
    $(document).ready(function() {
        $('.table').DataTable({
            "paging": true,
            "pageLength": 20,
            "lengthChange": true,
            "lengthMenu": [
                [10, 20, 50, 100, -1],
                [10, 20, 50, 100, 'All']
            ],
            "order": [
                [1, "desc"]
            ],
            "search": {
                "caseInsensitive": false
            },
            <?php

            if (trim(rex::getUser()->getLanguage()) == '' || trim(rex::getUser()->getLanguage()) == 'de_de') {
                if (rex::getProperty('lang') == 'de_de') {
                    echo '
                    language: {
                        "search": "Suchen:",
                        "decimal": ",",
                        "info": "Einträge _START_-_END_ von _TOTAL_",
                        "emptyTable": "Keine Daten",
                        "infoEmpty": "0 von 0 Einträgen",
                        "infoFiltered": "(von _MAX_ insgesamt)",
                        "lengthMenu": "_MENU_ anzeigen",
                        "loadingRecords": "Lade...",
                        "zeroRecords": "Keine passenden Datensätze gefunden",
                        "thousands": ".",
                        "paginate": {
                            "first": "<<",
                            "last": ">>",
                            "next": ">",
                            "previous": "<"
                        },
                    },
                    ';
                }
            }

            ?>
        });
    });
</script>