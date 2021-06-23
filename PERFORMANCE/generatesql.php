<?php

require_once __DIR__ . '/../vendor/autoload.php';


$page_array = ["/?page=1", "/?page=2", "/?page=3", "/?page=4", "/?page=5", "/?page=6", "/?page=7", "/?page=8", "/?page=9", "/?page=10", "/?page=11", "/?page=12", "/?page=13", "/?page=14", "/?page=15", "/?page=16", "/?page=17", "/?page=18", "/?page=19", "/?page=20", "/?page=21", "/?page=22", "/?page=23", "/?page=24", "/?page=25", "/?page=26", "/?page=27", "/?page=28", "/?page=29", "/?page=30", "/?page=31", "/?page=32", "/?page=33", "/?page=34", "/?page=35", "/?page=36", "/?page=37", "/?page=38", "/?page=39", "/?page=40", "/?page=41", "/?page=42", "/?page=43", "/?page=44", "/?page=45", "/?page=46", "/?page=47", "/?page=48", "/?page=49", "/?page=50", "/?page=51", "/?page=52", "/?page=53", "/?page=54", "/?page=55", "/?page=56", "/?page=57", "/?page=58", "/?page=59", "/?page=60", "/?page=61", "/?page=62", "/?page=63", "/?page=64", "/?page=65", "/?page=66", "/?page=67", "/?page=68", "/?page=69", "/?page=70", "/?page=71", "/?page=72", "/?page=73", "/?page=74", "/?page=75", "/?page=76", "/?page=77", "/?page=78", "/?page=79", "/?page=80", "/?page=81", "/?page=82", "/?page=83", "/?page=84", "/?page=85", "/?page=86", "/?page=87", "/?page=88", "/?page=89", "/?page=90", "/?page=91", "/?page=92", "/?page=93", "/?page=94", "/?page=95", "/?page=96", "/?page=97", "/?page=98", "/?page=99", "/?page=100", "/?page=101", "/?page=102", "/?page=103", "/?page=104", "/?page=105", "/?page=106", "/?page=107", "/?page=108", "/?page=109", "/?page=110", "/?page=111", "/?page=112", "/?page=113", "/?page=114", "/?page=115", "/?page=116", "/?page=117", "/?page=118", "/?page=119", "/?page=120", "/?page=121", "/?page=122", "/?page=123", "/?page=124", "/?page=125", "/?page=126", "/?page=127", "/?page=128", "/?page=129", "/?page=130", "/?page=131", "/?page=132", "/?page=133", "/?page=134", "/?page=135", "/?page=136", "/?page=137", "/?page=138", "/?page=139", "/?page=140", "/?page=141", "/?page=142", "/?page=143", "/?page=144", "/?page=145", "/?page=146", "/?page=147", "/?page=148", "/?page=149", "/?page=150", "/?page=151", "/?page=152", "/?page=153", "/?page=154", "/?page=155", "/?page=156", "/?page=157", "/?page=158", "/?page=159", "/?page=160", "/?page=161", "/?page=162", "/?page=163", "/?page=164", "/?page=165", "/?page=166", "/?page=167", "/?page=168", "/?page=169", "/?page=170", "/?page=171", "/?page=172", "/?page=173", "/?page=174", "/?page=175", "/?page=176", "/?page=177", "/?page=178", "/?page=179", "/?page=180", "/?page=181", "/?page=182", "/?page=183", "/?page=184", "/?page=185", "/?page=186", "/?page=187", "/?page=188", "/?page=189", "/?page=190", "/?page=191", "/?page=192", "/?page=193", "/?page=194", "/?page=195", "/?page=196", "/?page=197", "/?page=198", "/?page=199", "/?page=200", "/?page=201", "/?page=202", "/?page=203", "/?page=204", "/?page=205", "/?page=206", "/?page=207", "/?page=208", "/?page=209", "/?page=210", "/?page=211", "/?page=212", "/?page=213", "/?page=214", "/?page=215", "/?page=216", "/?page=217", "/?page=218", "/?page=219", "/?page=220", "/?page=221", "/?page=222", "/?page=223", "/?page=224", "/?page=225", "/?page=226", "/?page=227", "/?page=228", "/?page=229", "/?page=230", "/?page=231", "/?page=232", "/?page=233", "/?page=234", "/?page=235", "/?page=236", "/?page=237", "/?page=238", "/?page=239", "/?page=240", "/?page=241", "/?page=242", "/?page=243", "/?page=244", "/?page=245", "/?page=246", "/?page=247", "/?page=248", "/?page=249", "/?page=250", "/?page=251", "/?page=252", "/?page=253", "/?page=254", "/?page=255", "/?page=256", "/?page=257", "/?page=258", "/?page=259", "/?page=260", "/?page=261", "/?page=262", "/?page=263", "/?page=264", "/?page=265", "/?page=266", "/?page=267", "/?page=268", "/?page=269", "/?page=270", "/?page=271", "/?page=272", "/?page=273", "/?page=274", "/?page=275", "/?page=276", "/?page=277", "/?page=278", "/?page=279", "/?page=280", "/?page=281", "/?page=282", "/?page=283", "/?page=284", "/?page=285", "/?page=286", "/?page=287", "/?page=288", "/?page=289", "/?page=290", "/?page=291", "/?page=292", "/?page=293", "/?page=294", "/?page=295", "/?page=296", "/?page=297", "/?page=298", "/?page=299", "/?page=300", "/?page=301", "/?page=302", "/?page=303", "/?page=304", "/?page=305", "/?page=306", "/?page=307", "/?page=308", "/?page=309", "/?page=310", "/?page=311", "/?page=312", "/?page=313", "/?page=314", "/?page=315", "/?page=316", "/?page=317", "/?page=318", "/?page=319", "/?page=320", "/?page=321", "/?page=322", "/?page=323", "/?page=324", "/?page=325", "/?page=326", "/?page=327", "/?page=328", "/?page=329", "/?page=330", "/?page=331", "/?page=332", "/?page=333", "/?page=334", "/?page=335", "/?page=336", "/?page=337", "/?page=338", "/?page=339", "/?page=340", "/?page=341", "/?page=342", "/?page=343", "/?page=344", "/?page=345", "/?page=346", "/?page=347", "/?page=348", "/?page=349", "/?page=350", "/?page=351", "/?page=352", "/?page=353", "/?page=354", "/?page=355", "/?page=356", "/?page=357", "/?page=358", "/?page=359", "/?page=360", "/?page=361", "/?page=362", "/?page=363", "/?page=364", "/?page=365", "/?page=366", "/?page=367", "/?page=368", "/?page=369", "/?page=370", "/?page=371", "/?page=372", "/?page=373", "/?page=374", "/?page=375", "/?page=376", "/?page=377", "/?page=378", "/?page=379", "/?page=380", "/?page=381", "/?page=382", "/?page=383", "/?page=384", "/?page=385", "/?page=386", "/?page=387", "/?page=388", "/?page=389", "/?page=390", "/?page=391", "/?page=392", "/?page=393", "/?page=394", "/?page=395", "/?page=396", "/?page=397", "/?page=398", "/?page=399", "/?page=400", "/?page=401", "/?page=402", "/?page=403", "/?page=404", "/?page=405", "/?page=406", "/?page=407", "/?page=408"];
$browser_array = ["Firefox", "Chrome", "Chrome Mobile", "Opera", "Firefox Mobile", "Safari iOS", "Safari", "Microsoft Edge", "Samsung Browser"];
$os_array = ["Windows 10", "Ubuntu", "MacOS", "iOS 14", "Android 10", "Android 9", "Android 11", "Windows 7", "GNU Linux"];
$dt_array = ["Mobile", "Desktop", "Tablet", "Phablet"];
$brand_array = ["LG", "Apple", "Samsung", "Huawei", "Xioami", "Nokia", "Oppo", "Sony"];
$model_array = ["Lorem", "ipsum", "dolor", "sit", "amet", "consetetur", "sadipscing", "elitr", "sed", "diam", "nonumy", "eirmod", "tempor", "invidunt", "ut", "labore", "et", "olore", "magna", "aliquyam", "erat", "sed", "diam", "voluptua", "At", "vero", "eos", "et", "accusam", "et"];
$hour_array = ["1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12", "13", "14", "15", "16", "17", "18", "19", "20", "21", "22", "23"];
$weekday_array = ["1", "2", "3", "4", "5", "6", "7"];


$start = new DateTime('12.06.2021');
$end = new DateTime('22.06.2021');
function randomDateInRange(DateTime $start, DateTime $end)
{
    $randomTimestamp = mt_rand($start->getTimestamp(), $end->getTimestamp());
    $randomDate = new DateTime();
    $randomDate->setTimestamp($randomTimestamp);
    return $randomDate;
}


for ($i = 0; $i < 5000; $i++) {
    $date = randomDateInRange($start, $end)->format('Y-m-d');

    $page = $page_array[array_rand($page_array)];
    $browser = $browser_array[array_rand($browser_array)];
    $os = $os_array[array_rand($os_array)];
    $dt = $dt_array[array_rand($dt_array)];
    $brand = $brand_array[array_rand($brand_array)];
    $model = $model_array[array_rand($model_array)];
    $hour = $hour_array[array_rand($hour_array)];
    $weekday = $weekday_array[array_rand($weekday_array)];

    $sql = "INSERT INTO rex_pagestats_dump (browser,os,browsertype,brand,model,url,date,hour,weekday) VALUES ('" . $browser . "','" . $os . "','" . $dt . "','" . $brand . "','" . $model . "','" . $page . "','" . $date . "','" . $hour . "','" . $weekday . "');" . PHP_EOL;

    file_put_contents(__DIR__ . '/data.sql', $sql, FILE_APPEND);
}
