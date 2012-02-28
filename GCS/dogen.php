<?php
//Генератор гик-кода
//скрипт-обработчик
//GCUF ver.: 1.0.4
//UPD: 18.02.2005
//Bridge: sql2php

class gen
{

var $def;

//гик-код юзера
var $code;

//массив с уже выведенными параметрами:
var $pars_shown;

//GCUF-friendly $_GET:
var $get;

//ИНИЦИАЛИЗАЦИЯ
function init()
{
//подключаем конфиг
require "cfg.php";

//определимся, какой xml юзать:
if ($_GET['xml']) $xml=$this->XML_PATH.$_GET['xml'].".xml"; else $xml=$this->XML_PATH."geekcode.xml";

//подключаем bridge и делаем $def
require($this->SCRIPT_PATH.$this->BRIDGE);
$this->def=makedef($xml);


$this->pars_shown=array();

//подключим IPB SDK
require_once $this->SDK_PATH."ipbsdk_class.inc.php";
$this->SDK =& new IPBSDK();

foreach ($_GET as $key=>$value)
{
    //заменим "p" на плюсы...
    $value=str_replace("p","+",$value);
    //...и заменим null на пустое место
    $value=str_replace("null","",$value);
    //вырежем плохих ребят
    if (($key!=='act') and ($key!=='site')) $this->get[$key]=$value;

}

}

//ПАРСЕРЫ
//все парсеры получают два параметра:
//$par - кусок из $_GET, и $id - параметр, к которому этот кусок относится
function parse_simple($par,$id)
{
$def=$this->def;

//а мы кстати не выводили это параметр уже?
if (!in_array($id,$this->pars_shown))
   {
      //проверяем возможное числовое значение параметра:

      //имеет ли переменная название с num на конце:
      if (substr($par,strlen($id))=='num')
          {
          //можно ли указывать числовое значение у этого параметра:
          if ($def[$id]['allow_num']=='1')
          {
                  //вроде всё ок... ну и что, прям число и передали?
                  if (is_numeric($this->get[$par]))
                  {
                          //да, всё ок, добавляем...
                          $this->code[]=$id.$_GET[$par];
                  }
          }
          }
      //никаких num на конце, ну тогда просто добавляем в вывод параметр со значением...
      else $this->code[]=$id.$this->get[$par];
      //не забываем записать параметр в список уже выведенных
      $this->pars_shown[]=$id;
   }
}

function parse_flag($par,$id)
{
$def=$this->def;
if (!in_array($def[$par]['group'],$this->pars_shown))
{
     $this->code[]=$this->get[$par];
     $this->pars_shown[]=$def[$par]['group'];
}
}

function parse_split($par,$id)
{
$def=$this->def;
if (!in_array($id,$this->pars_shown))
{
       foreach ($this->get as $key=>$value)
       {
               if (substr($key,0,strlen($id))==$id)
               {
                       $parameter.=$divisor.$value;
                       $divisor = ":";
               }
       }
       $this->code[]=$id.$parameter;
       //не забываем записать параметр в список уже выведенных
       $this->pars_shown[]=$id;
}
}

function parse_bracket($par,$id)
{
$def=$this->def;
if (!in_array($id,$this->pars_shown))
{
      //проверяем возможное числовое значение параметра:
      //есть ли переменная с num на конце:
      if (array_key_exists($id.'num',$this->get))
      {
          //можно ли указывать числовое значение у этого параметра:
          if ($def[$id]['allow_num']=='1')
          {
                  //вроде всё ок... ну и что, прям число и передали?
                  if (is_numeric($this->get[$id.'num']))
                  {
                          //да, всё ок, добавляем...
                          $bracket[]=$this->get[$id.'num'];
                  }
          }
      }
      //нет, циферок нет, будем искать список субпараметров...
      else foreach ($this->get as $key=>$value)
        {
                if ((substr($key,0,strlen($id))==$id) and (is_numeric(substr($key,strlen($id)))))
                {
                        $bracket[]=$value;
                }
        }
        //закончили дергание субпараметров, превращаем это в строку:
        if ($bracket) {
                $bracket=implode("|",$bracket);
                $bracket="(".$bracket.")";
                       }
        //теперь добавим основное значение:
        if (!(substr($par,strlen($id))=='num') and !(is_numeric(substr($par,strlen($id))))) $this->code[]=$par.$bracket.$this->get[$id];
        //не забудем добавить в массив с уже выведенными параметрами запись:
        $this->pars_shown[]=$id;

}
}
//ШАБЛОН

//шапка страницы, гик-код большими буквами
function output_code($code)
{
echo "
<center><b>Генерация завершена!<br>
Ваш гик-код:</b>
<p><font size='+1'>$code</font></p>
<br>
Теперь у вас есть свой гик-код :)
<br><br><hr>
";
}

//предложение расшифровать гик-код
function output_decode_me($code)
{
echo "
<center>Можете проверить правильность генерации, расшифровав только что созданный гик-код.<br><br>
<b><a href='index.php?act=portal&site=17&gcode={$code}'>»Расшифровать«</a></b><hr>
";

}

function output_save_me($id,$code)
{
echo "
<center>Рекомендуется сохранить ваш гик-код в базе данных! Это позволит вам использовать более короткие ссылки
на страницу с расшифровкой вашего кода, вы сможете изменять и просматривать свой код через панель управления!
<br><br>
<b><a href='index.php?act=portal&site=22&gcode={$code}&id={$id}'>»Сохранить«</a></b><hr>
";
}

function output_no_save($foo_bar)
{
echo "
<center>К сожалению, незарегистрированные пользователи не могут сохранять свой гик-код в нашей базе данных. <a href='index.php?act=Reg&CODE=00'>Зарегистрируйтесь</a> и получите доступ к панели управления своим гик-кодом, GCard, файловому архиву. Только зарегистрированные пользователи могут использовать короткие ссылки на расшифровку гик-кода.
";
}

//ссылки для юзания на форумах и т.п.
//$rcode = rawurlencode("гик-код")
function output_link($code,$rcode)
{
echo "
<center>
<br>Размещая свой гик-код на веб-страницах, в подписи на форумах или письмах, вы наверняка захотите связать свой гик-код с его расшифровкой. Для этого используйте нижеприведённые коды:
<table>
<tr>
<td><b>Код ссылки</b></td>
<td><b>Вид ссылки</b></td>
</tr>
<tr>
<td><b>HTML:</b><br>
<textarea name='code1' rows=4 cols=50 wrap='on'><a href='http://www.geekclub.ru/index.php?act=portal&site=17&gcode={$rcode}'>{$code}</a></textarea>
</td>
<td align='center'>
<a href='http://www.geekclub.ru/index.php?act=portal&site=17&gcode={$rcode}'>{$code}</a>
<br>
<input type=\"button\" value=\"Копировать\" onclick=\"code1.select(); document.execCommand('copy');\">
</td>
</tr>
<tr>
<td><b>BB code:</b><br>
<textarea name='code2' rows=4 cols=50 wrap='on'>[URL=http://www.geekclub.ru/index.php?act=portal&site=17&gcode={$rcode}]{$code}[/URL]</textarea>
</td>
<td align='center'>
<a href='http://www.geekclub.ru/index.php?act=portal&site=17&gcode={$rcode}'>{$code}</a>
<br>
<input type=\"button\" value=\"Копировать\" onclick=\"code2.select(); document.execCommand('copy');\">
</td>
</tr>
</table>
</center>
<hr>";
}

//MISC

//получение инфы о юзере
function getinfo()
{
$this->info=$this->SDK->get_info();
}

//ДИСПЕТЧЕР
function parse_all()
{
$def=$this->def;

foreach ($this->get as $key=>$value)
{

{
        //а теперь собственно решаем вечный вопрос: что делать?
        for ($x=2; $x>0; $x--)

            {
                 if (array_key_exists(substr($key,0,$x),$this->def))
                 {
                                  switch ($this->def[substr($key,0,$x)]['type'])
                                  {
                                            case 'simple': {$this->parse_simple($key,substr($key,0,$x)); continue;}
                                            case 'flag': {$this->parse_flag($key,substr($key,0,$x)); continue;}
                                            case 'split': {$this->parse_split($key,substr($key,0,$x)); continue;}
                                            case 'bracket': {$this->parse_bracket($key,substr($key,0,$x)); continue;}
                                  }
                 continue 2;
                 }
              }

}
}

}

//ФУНКЦИЯ-ИСПОЛНИТЕЛЬ
function run()
{
        $this->init();
        $this->parse_all();
        $this->getinfo();
        //выводим код
        $this->output_code(join(" ",$this->code));
        //предлагаем проверить правильность, расшифровав код
        $this->output_decode_me(rawurlencode(join(" ",$this->code)));
        //и ещё предлагаем линки на выбор
        $this->output_link(join(" ",$this->code),rawurlencode(join(" ",$this->code)));
        //если это не гость, то давайте уж предложим ему сохранить в базе его код!
        if ($this->SDK->is_loggedin()) $this->output_save_me($this->info['id'],rawurlencode(join(" ",$this->code)));
            else $this->output_no_save("Guests suck!");

}
}
$gen = new gen;
$gen->run();

?>