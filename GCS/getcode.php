<?php
//функция: получение гик-кода из базы данных
//GCUF ver.: 1.0.3
//UPD: 15.02.2005


//всемогущий require!
//require ("/home/g/gamebiz/ipb2/public_html/conf_global.php");


//GET!
function getcode($id)
{

//подключаем конфиг
require "cfg.php";

//коннектимся и выбираем базу
mysql_connect($this->dbhost,$this->dbuser,$this->dbpass);

mysql_select_db($this->dbname);

//запись гик-кода в базу

$q=mysql_query("SELECT ".$this->db_gcode_column." FROM ".$this->dbtable." WHERE id=\"{$id}\";");
$q=mysql_fetch_assoc($q);
$geekcode=$q['geekcode'];

return $geekcode;

}
?>