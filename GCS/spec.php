<?php
//спецификатор гик-кода
//UPD: 12.02.2005
//для GCUF версии: 1.03
//Bridge: sql2php


class spec {
var $def;
var $cat;         //текущая категория параметров
var $flags_shown; //группы показанных flag-параметров

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



settype($this->flags_shown,'array');


}

//ПАРСЕРЫ, go!
function parse_simple($par)
{
$def=$this->def;
//а мы не забыли вывести название категории?
$this->check_cat($def[$par]['cat']);
//для начала выведем базовую информацию:
$this->output_basic(array ('id'=>$par, 'title'=>$def[$par]['title'], 'cat'=>$def[$par]['cat'],'desc'=>$def[$par]['desc'], 'desc2'=>$def[$par]['desc2'], 'type'=>$def[$par]['type']));
//теперь - инфу о параметрах:
$this->output_par_header();
foreach ($def[$par] as $key=>$val)
{
        switch ($key)
        {
        //эту всю херь пропускаем за ненадобностью...
        case 'title':
        case 'words':
        case 'type':
        case 'allow_num':
        case 'desc':
        case 'desc2':
        case 'cat':
        case 'words_def':
        case 'group':
        case 'example': continue;
        //символьный параметр? на вывод! только null на пустое место заменим...
        default : $this->output_parameter(array('key'=>str_replace("null"," ",$key),'value'=>$val));
        }
}
$this->output_par_foot();

//а может можно число выводить?
if ($def[$par]['allow_num'] == '1' )
{
$this->output("<br>Данный параметр допускает указание численного значения сразу после идентификатора.","b");
}

//на сладкое выведем пример
$this->output_ex($def[$par]['example']);
}

function parse_split($par)
{
$def=$this->def;
//а мы не забыли вывести название категории?
$this->check_cat($def[$par]['cat']);
//для начала выведем базовую информацию:
$this->output_basic(array ('id'=>$par, 'title'=>$def[$par]['title'], 'cat'=>$def[$par]['cat'],'desc'=>$def[$par]['desc'], 'desc2'=>$def[$par]['desc2'], 'type'=>$def[$par]['type']));
$this->output("<br>В составе этого параметра - несколько субпараметров, разделяемых знаком : (двоеточие). Пробелов нет.","b");
//теперь - инфу о субпараметрах:
//перебираем все элементы $def[$par]['позиция субпараметра']...
{
$x=0;
while (array_key_exists('sub'.$x,$def[$par]))
{
   $this->output_par_header("Допустимые значения ".($x+1)."го субпараметра");
   foreach ($def[$par]['sub'.$x] as $key=>$val)
       {
       $this->output_parameter(array('key'=>str_replace("null"," ",$key),'value'=>$val));
       }
   $this->output_par_foot();
$x++;
}
}
//на сладкое выведем пример
$this->output_ex($def[$par]['example']);
}

function parse_bracket($par)
{
$def=$this->def;
//а мы не забыли вывести название категории?
$this->check_cat($def[$par]['cat']);
//выведем базовую информацию:
$this->output_basic(array ('id'=>$par, 'title'=>$def[$par]['title'], 'cat'=>$def[$par]['cat'],'desc'=>$def[$par]['desc'], 'desc2'=>$def[$par]['desc2'], 'type'=>$def[$par]['type']));
//теперь - параметры:
$this->output_par_header();
foreach ($def[$par] as $key=>$val)
{
        switch ($key)
        {
        //эту всю херь пропускаем за ненадобностью...
        case 'title':
        case 'words':
        case 'type':
        case 'allow_num':
        case 'desc':
        case 'desc2':
        case 'cat':
        case 'words_div':
        case 'words_b':
        case 'b':
        case 'example': continue;
        //символьный параметр? на вывод! только null на пустое место заменим...
        default : $this->output_parameter(array('key'=>str_replace("null"," ",$key),'value'=>$val));
        }
}
$this->output_par_foot();
//список допустимых субпараметров в скобках...
if (array_key_exists('b',$def[$par]))
{
$this->output("<br>Данный параметр допускает указание одного или нескольких дополнительных субпараметров в скобках сразу после идентификатора. Субпараметры разделяются знаком | (вертикальная черта). Пробелов нет.","b");
$this->output_par_header("Допустимые значения субпараметра");
foreach ($def[$par]['b'] as $key=>$val)
{
         //не забываем менять null на пустое место...
         $this->output_parameter(array('key'=>str_replace("null"," ",$key),'value'=>$val));
}
$this->output_par_foot();
}
//а может можно число выводить?
if ($def[$par]['allow_num'] == '1' )
{
$this->output("<br>Данный параметр допускает указание численного значения в скобках сразу после идентификатора.","b");
}
//ну и пример на десерт...
$this->output_ex($def[$par]['example']);
}

function parse_flag($par)
{
$def=$this->def;

//мы уже выводили эту группу флагов?
if (!in_array($def[$par]['group'],$this->flags_shown))
{
           //формируем список флагов, входящих в группу:
           foreach ($def as $key=>$val)
           {
                   if ($def[$key]['type']=='flag')
                   {
                           if ($def[$key]['group']==$def[$par]['group'])
                           {
                                   $group[]=$key;
                           }
                   }
           }
           //выведем базовую инфу. в id передадим список флагов, который только что сформировали
           $this->output_basic(array('id'=>join(", ",$group), 'title'=>$def[$par]['title'], 'type'=>$def[$par]['type'], 'desc'=>$def[$par]['desc'], 'desc2'=>$def[$par]['desc2']));

           //теперь - лексические значения флагов:
           $this->output_par_header();
           foreach ($group as $flag)
           {
            foreach ($def[$flag] as $key=>$val)
            {
                switch ($key)
                        {
                        //эту всю херь пропускаем за ненадобностью...
                        case 'title':
                        case 'words':
                        case 'type':
                        case 'allow_num':
                        case 'desc':
                        case 'desc2':
                        case 'cat':
                        case 'words_def':
                        case 'group':
                        case 'example': continue;
                        //символьный параметр? на вывод! только null на пустое место заменим...
                        default : $this->output_parameter(array('key'=>$flag,'value'=>$val));
                        }
            }
           $example[]=$def[$flag]['example'];
           }
           $this->output_par_foot();
           //ну и куда же без примера:
           $this->output_ex(join("<br>",$example));
           //всё сделали, теперь не забудем добавить это флаг в список уже выведенных:
           $this->flags_shown[]=$def[$par]['group'];
}

}

//вывод текущей категории:
function check_cat($cat)
{
if ($this->cat != $cat)
        {
        $this->cat=$cat; $this->output_cat($cat);
        }
}

//ШАБЛОННЫЕ ФУНКЦИИ
//аналог echo. но писать echo - не круто, когда можно написать $this->output("blah") :)
//style: b, i или u.
function output($txt,$style="font")
{
echo "
<{$style}>$txt</{$style}>
";
}

//вывод базовой инфы о параметре
//передаём массив $par с элементами id, title, type, cat, desc, desc2
function output_basic($par)
{
echo "
<br><hr><b><font size='+1'>{$par['id']}: {$par['title']}</font></b><br>
<b><small>Тип: {$par['type']}</small></b><br>
{$par['desc']}<br>
{$par['desc2']}
";
}

//шаблон названия категории
function output_cat($name)
{
echo "<center><h2>{$name}</h2></center>";
}

//вывод примера
function output_ex($str)
{
echo "
<br><br><i>Пример</i>:<br> <font face='Courier New'>{$str}</font>
";
}

//шапка таблицы со значениями параметра. $title - заголовок таблицы, $row1 и $row2 - заголовки столбцов
function output_par_header($title="Допустимые значения параметра",$row1="Символьное значение",$row2="Лексическое значение")
{
echo "
<br><b>{$title}:</b>
<table><tr><td width=15%>{$row1}</td><td>{$row2}</td></tr>
";
}

//конец таблицы со значениями параметра
function output_par_foot()
{
echo "
</table>
";
}
//этой функции передаётся массив $par с элементами key - символьное значение и value - лексическое
function output_parameter($par)
{
echo "
<tr><td><b>{$par['key']}</b></td><td>{$par['value']}</td></tr>
";
}

//ФУНКЦИЯ-ДИСПЕТЧЕР
function parse_all()
{
$def=$this->def;
foreach ($def as $key=>$val)
{
        switch ($def[$key]['type'])
        {
                case 'simple': $this->parse_simple($key); continue;
                case 'flag': $this->parse_flag($key); continue;
                case 'split': $this->parse_split($key); continue;
                case 'bracket' : $this->parse_bracket($key); continue;
                default : $this->parse_simple($key); continue;

        }
}
}
}

$spec = new spec;
$spec->init();
$spec->parse_all();


?>