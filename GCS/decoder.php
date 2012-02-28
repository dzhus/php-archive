<?php
//расшифровщик гик-кода
//UPD 26.07.05
//для GCUF версии: 1.0.4
//Bridge: sql2php


//САМЫЙ ГЛАВНЫЙ КЛАСС
class decoder {

var $def;
var $code;


//ИНИЦИАЛИЗАЦИЯ
function init()
{
//код-то нам хоть передали?
if ((!$_GET['gcode']) and (!$_GET['id'])) die("Ну и где ваш код? Чего вы мне хуйню какую-то подсовываете??");

//подключаем конфиг
require "cfg.php";

//определимся, какой xml юзать:
if ($_GET['xml']) $xml=$this->XML_PATH.$_GET['xml'].".xml"; else $xml=$this->XML_PATH."geekcode.xml";

//подключаем bridge и делаем $def
require($this->SCRIPT_PATH.$this->BRIDGE);
$this->def=makedef($xml);

//а теперь смотрим, что же нам подсунули
//если code, то переводим URL-encoded строку в нормальный формат
if ($_GET['gcode'])
    $this->code=rawurldecode($_GET['gcode']);
//если id пользователя, то подключаем сервисный скрипт и дергаём код из базы
else {
        require($this->SCRIPT_PATH."getcode.php");
        $this->code=getcode($_GET['id']);
     }

//взорвём этот гадюшник:
$this->code=explode(" ",$this->code);

//setlocale(LC_ALL,"ru_RU");
}



/*
PARSERS IN DA HOUSE!
Далее идут функции разбора разных типов параметров.
Каждому парсеру передаём: $part - кусок гик-кода для парсинга и $par - параметр
Каждая функция передаёт управление шаблонной функции output с таким параметром:
array ( 'title' => [название распарсенного параметра],
        'value' => [значени(е/я) параметра на обычном языке :) ],
        'raw'   => [входящий участок гик-кода, который функция и парсила]
        );
*/

//функция парсинга simple параметров, таких как e, r, t, N, W, H, g
function parse_simple($part,$par)
{

$def=$this->def;
if (strlen($part)==strlen($par))
            {
            $value=str_replace('<% bas %>',$def[$part]['null'],$def[$par]['words']); //параметр пустой?
            }
        else
            if (is_numeric(substr($part,strlen($par))) == TRUE) //после идентификатора идёт число?
               {
               $value=str_replace('<% num %>',substr($part,strlen($par)),$def[$par]['words_def']);
               }
            else                                     //или всё-таки стандартное значение
               {
               $value=str_replace('<% bas %>',$def[$par][substr($part,strlen($par))],$def[$par]['words']);

               }
        $value=ucfirst($value);
        $this->output(array('title'=>$def[$par]['title'],
                     'value'=>$value,
                     'raw'=>$part
                     ));

}


//функция парсинга split параметров, например b или s
function parse_split($part,$par)
{
         $def=$this->def;
         if (substr($part,strlen($par))=='?' AND strlen($part)==strlen($par)+1) //после параметра идет знак вопроса?
           {
           $value=str_replace("<% bas %>",$def[$par]['?'],$def[$par]["words"]);
           }
         else
         {
             $splitvalue=explode(":",substr($part,strlen($par))); //разделим сплитовую часть в массив
             foreach ($splitvalue as $i=>$val)
             {
                if (!$val)
                   {
                        $value.=str_replace("<% sub{$i} %>",$def[$par]['sub'.$i]['null'],$def[$par]["words_{$i}"]); //часть пуста?
                   }
                else
                   {
                       $value.=str_replace("<% sub{$i} %>",$def[$par]['sub'.$i][$val],$def[$par]["words_{$i}"]);
                   }
             }
         }
        $value=ucfirst($value);
        $this->output(array('title'=>$def[$par]['title'],
                     'value'=>$value,
                     'raw'=>$part
                     ));

}

//функция парсинга bracket параметров
function parse_bracket($part,$par)
{
        $def=$this->def;
        if (strlen($part)==strlen($par) OR (substr($part,-1)=="\x29")) //а что, только параметры в скобках и есть? или вообще ничего нет?
                {
                //действительно, ну тогда включим в вывод null
                $value.=str_replace('<% bas %>',$def[$par]['null'],$def[$par]['words']);
                }
        elseif (strstr($part,"\x29"))  //включаем в вывод лексическое значение символьного параметра
                 //если скобки есть
                 {

                 $value.=str_replace('<% bas %>',$def[$par][substr($part,(strpos($part,"\x29")+1))],$def[$par]['words']);
                 }
               else      //и если скобок нет
                 {

                 $value.=str_replace('<% bas %>',$def[$par][substr($part,(strlen($par)))],$def[$par]['words']);
                 }
         if (strpos($part,"\x28")!=FALSE) //скобки есть?
         {
            $splitvalue=substr($part,strpos($part,"\x28")+1,(strpos($part,"\x29")-strpos($part,"\x28")-1)); //вырываем то, что в скобках
            $splitvalue = explode("|",$splitvalue);
            foreach ($splitvalue as $i)
                     {

                     if (is_numeric($i)) //опа! а это у нас цифра что-ли? старый bracketnumber?
                                          {
                                          $val.=$divisor;
                                          $val.=$i;
                                          if (!$divisor) $divisor=$def[$par]['words_div'];
                                          }
                     else                 //не, всё-таки это список буквенных значений...
                         {
                                          $val.=$divisor;
                                          $val.=$def[$par]['b'][$i];
                                          if (!$divisor) $divisor=$def[$par]['words_div'];
                         }
                     }

            $val=str_replace('<% sub %>',$val,$def[$par]['words_b']);
            $value.=str_replace('<% bas %>',$val,$def[$par]['words']);
         }
        $value=ucfirst($value);
        $this->output(array('title'=>$def[$par]['title'],'value'=>$value,'raw'=>$part));
}


//ФУНКЦИЯ-ДИСПЕТЧЕР
//парсим код, функция парсинга определяется значением type в субмассиве параметра
function parse_all()
{
      foreach ($this->code as $c)
        {
        for ($x=2; $x>0; $x--)

            {
                 if (substr($c,0,$x)) //проверяем, не пуст ли кусок
                 {                
                 if (array_key_exists(substr($c,0,$x),$this->def))
                  
                 {
                        switch ($this->def[substr($c,0,$x)]['type'])
                                  {

                                          case "simple": {$this->parse_simple($c,substr($c,0,$x)); continue;}
                                          case "split": { $this->parse_split($c,substr($c,0,$x)); continue;}
                                          case "bracket": {$this->parse_bracket($c,substr($c,0,$x)); continue;}
                                          case "flag": {$this->parse_simple($c,substr($c,0,$x)); continue;}
                                          default: {$this->parse_simple($c,substr($c,0,$x)); continue;}
                                  }
                        continue 2;
                 }}
            }

        }

}
//ШАБЛОННЫЕ ФУНКЦИИ
//вывод на экран расшифрованного параметра
function output($item)
{

         echo "
         <b><font size='+1'>{$item['title']}:</font></b><br><br>
         <b><i>{$item['raw']}:</i></b> {$item['value']}
         <hr>
         ";


}

//шапка страницы с распарсенным кодом
//просто выводится переданный скрипту код:
function head($code)
{
         echo "
         <p><font size='+1'>{$code}</font></p><br>
         ";
}

function run()
{
$this->init();
$this->head(join(" ",$this->code));
$this->parse_all();
}

}

$decoder = new decoder;
$decoder->run();


?>