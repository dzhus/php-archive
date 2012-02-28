<?php

//нафиг кэширование
Header("Expires: Mon, 19 Mar 1989 12:24:00 GMT");
Header("Cache-Control: no-cache, must-revalidate");
Header("Pragma: no-cache");
Header("Last-Modified: ".gmdate("D, d M Y H:i:s")."GMT");

session_start();
      
//подключаем рутины и базовые объекты:
require("cfg.php");
require("functions.php");
$std->skin_write();
require('skins/'.$std->skin_read()."/skin_global.php");
require("mysql.php");
require("mail.php");
require("user.php");
//итого имеем объекты $std,$user,$mail и $mysql, а также массив $cfg, а также глобальный скин $global_skin

require("modlist.php");
require("side_modlist.php");




//подключим основной модуль

($std->input['a']) ? $modname=$std->input['a'] : $modname = $cfg['default_modname'];

if ($modname) $main_module=$modlist[$modname]; else $main_module=$modlist['default'];

//подключаем файлы модуля и скина
require 'skins/'.$std->skin_read()."/skin_".$main_module['file'];
require($main_module['file']);

//декларация и запуск
$skinname = "skin_".$modname;
$skin = new $skinname;
$class = new $modname;
$class->run();

$html['main']=$class->html;


//выгрузим модуль
unset($skin);
unset($class);
unset($modname);






//если враппинг включён, то
//аналогично действуем с дополнительными общими блоками
if(!$main_module['disable_wrapping'])
{
	foreach ($side_modlist as $modname => $content)
	{
	//подключение файлов
	require 'skins/'.$std->skin_read()."/skin_".$content['file'];
	require($content['file']);
	
	//декларация и запуск
	$skinname = "skin_".$modname;
	$skin = new $skinname;
	$class = new $modname;
	$class->run();
	$html[$modname] = $class->html;

	//выгрузка
	unset($skin);
	unset($class);
	
	
	

	}

}



//вывод
//если враппинг отключён, просто выплёвываем вывод главного модуля
if (!$main_module['disable_wrapping']) 
	$std->print_all('skins/'.$std->skin_read()."/wrapper.html");
else echo $html['main'];


?>
