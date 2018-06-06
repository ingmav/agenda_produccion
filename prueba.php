<?php
$year = 2018;
$week = 20;
$fechaInicioSemana  = date('d-m-Y', strtotime($year . 'W' . str_pad($week , 2, '0', STR_PAD_LEFT)));

echo date('d-m-Y',strtotime("$fechaInicioSemana   +6 day"));
//echo str_pad($week , 2, '0', STR_PAD_LEFT);

/*$actual = strtotime(date("Y-m-d"));
echo $actual;
echo "<br>";
echo date("N", $actual);*/
?>