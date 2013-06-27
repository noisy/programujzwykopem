<?php

set_time_limit(15);

date_default_timezone_set('Europe/Warsaw');


include_once 'sztafetaClass.php';

$sztafeta = new sztafeta();
$sztafeta->dziennyRaport();

?>
