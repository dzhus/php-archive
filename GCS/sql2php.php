<?php
//Bridge-script
//Type: MySQL to PHP
//UPD: 15.02.2005
//GCUF: 1.0.3



function makedef()
{

//подключаем конфиг
require "cfg.php";

//коннектимся и выбираем базу
mysql_connect($dbhost,$dbuser,$dbpass);
mysql_select_db($dbname);

//$order_par=array("+++++","++++","+++","++","+","null","-","--","---","----","?");
//маска сортировки параметров
$order_global=array("a","x","y","z","s","e","r","b","t","C","N","W","L","H","P","GF","g");

//функция упорядочивания
function order($in,$order=array("+++++","++++","+++","++","+","null","-","--","---","----","?"))
{
$out=array();
foreach ($order as $key=>$value)
{
        if (array_key_exists($value,$in))
        {
                $out[$value]=$in[$value];
        }
}

foreach ($in as $key=>$value)
{
        if (!array_key_exists($key,$out))
        {
                $out[$key]=$in[$key];
        }
}
return $out;
}

//Let's get it started!

//подёргаем параметры
$q=mysql_query("SELECT * FROM gc_params");

while ($cur=mysql_fetch_assoc($q)) //построчно обрабатываем результат
{
foreach ($cur as $d=>$val) { //перебираем все поля строки
        switch ($d) {
          case 'id': continue; //это идентификатор что ли?
          default: if (!$val=='')
                   {
                   $def[$cur['id']][$d] = $val;
                   continue;
                   } //если нет, то заносим элемент в $def['идентификатор']
          }
}
}

//теперь займёмся значениями параметров
$q=mysql_query("SELECT * FROM gc_values");

while ($cur=mysql_fetch_assoc($q)) //построчно обрабатываем результат
{
foreach ($cur as $d=>$val)
         {
         switch ($d) {

                 case "val": if ($cur['type'] == 'bas')
                          {
                          $def[$cur['id']][$cur['key']] = $val;
                          continue;
                          }
                          else
                          {
                          $def[$cur['id']][$cur['type']][$cur['key']] = $val;

                          }
                 default: continue;
                 }
         }
}
//итого мы имеем массив $def, структурой отвечающий требованиям спецификации GeekCode
mysql_close();



//теперь отсортируем $def:

$def=order($def,$this->order_global);

foreach ($def as $key=>$value)
{
        $def[$key]=$this->order($def[$key]);
        foreach ($def[$key] as $par=>$mean)
        {
                if ($par==="b") $def[$key]['b']=$this->order($def[$key]['b']);
                if (is_numeric($par)) $def[$key][$par]=$this->order($def[$key][$par]);
        }

}

//возвращаем $def;
return $def;

}

?>