<?php
//скрипт: сохранение гик-кода в базе данных
//GCUF ver.: 1.0.3
//UPD: 15.02.2005


//всемогущий require!
//require ("/home/g/gamebiz/ipb2/public_html/conf_global.php");

//подключаем конфиг
require "cfg.php";

//подключим IPB SDK
require $this->SDK_PATH."ipbsdk_class.inc.php";
$SDK = new IPBSDK();

//GET!
$code=rawurldecode($_GET['gcode']);
$id=$_GET['id'];
$info=$SDK->get_info();

if ($info['id'] !== $id)
{
        echo "<center><b>Почему бы вам не попробовать взломать какой-нибудь другой ресурс, например microsoft.com?</b></center>";
        die;
}

//коннектимся и выбираем базу
mysql_connect($this->dbhost,$this->dbuser,$this->dbpass);

mysql_select_db($this->dbname);

//запись гик-кода в базу

$q=mysql_query("UPDATE ".$this->dbtable." SET ".$this->db_gcode_column."=\"{$code}\" WHERE id=\"{$id}\";");
echo "<center>Гик-код  $code успешно записан в базу данных юзеру $id!";
echo "<br><br><br><b><a href='index.php'>На главную</a></b>";
echo "<br><br><b><a href='index.php?act=portal&site=23'>В панель управления гик-кодом</a></b></center>";




?>