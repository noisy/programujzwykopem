<?php
         set_time_limit(15);
         ini_set('memory_limit', '256M');
         ini_set('error_reporting', E_ALL);
         ini_set('display_errors', 1);
         date_default_timezone_set('Europe/Warsaw');
         
         
include_once 'sztafetaClass.php';

$wyniki = new sztafeta();
//echo '<pre>';
//$array = $wyniki->pobierzWszystkieWpisy();

$array = $wyniki->pobierzNajnowszeWpisy();
$array = array_reverse($array);

$wyniki->dodajNowegoZawodnika($array);
$wyniki->dodajNowyWpis($array);

//echo '<br>';
//var_dump($wyniki->przebiegnietyDystans());
//echo '<br>';

//echo '</pre>';


?>
