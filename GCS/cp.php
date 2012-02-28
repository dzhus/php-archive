<?php
//скрипт панели управления гик-кодом
//GCUF 1.0.3
//UPD 29.07.05

class cpanel
{

var $uinfo; //инфа о пользователе

//ИНИЦИАЛИЗАЦИЯ
function init()
{

//подключаем конфиг
require "cfg.php";

//подключим IPB SDK
require_once $this->SDK_PATH."ipbsdk_class.inc.php";
$this->SDK = new IPBSDK();

//получаем инфу о юзере
$this->uinfo = $this->SDK->get_info();


}

//РАЗДЕЛЫ

//головная страница

function main()
{

if (!$this->SDK->is_loggedin()) $this->output_noguests(); else

{

//поприветствуем достопочтенную публику
//$this->output_epigraph();
$this->output_welcome();

//получаем гик-код юзера
require $this->SCRIPT_PATH."getcode.php";
$this->code=getcode($this->uinfo['id']);

$this->br(3);


//гик-код в рамочке
$this->output_heading("Ваш гик-код","b");
if ($this->code) $this->output_showcode($this->code); else $this->output_showcode("Код не задан!");

//пути изменения кода
$this->br(3);
$this->output_heading("Изменение гик-кода");
if ($this->code) $this->output_change_me($this->code,$this->uinfo['id']);
    else
    $this->output_change_me("",$this->uinfo['id'],"Если вы хорошо знакомы со спецификацией гик-кода, можете напрямую вписать свой код в поле ниже");

if ($this->code) $this->output_gen_me();
    else
    $this->output_gen_me("Вы можете использовать генератор гик-кода для быстрого и удобного создания своего кода!");

//расшифровка
$this->br(3);
$this->output_heading("Расшифровка");
if ($this->code) $this->output_decode_me($this->uinfo['id']);

//линки
$this->br(3);
$this->output_heading("Ссылки");
$this->output_link($this->uinfo['id'],$this->code);

}
}
//ШАБЛОН

//продвинутый <br>. сколько раз скажем, столько раз <br> и выведет.
function br($num=1)
{
for ($i=0; $i<$num; $i++) {
        echo "<br>";
        }

}

//аналог echo. но писать echo - не круто, когда можно написать $this->output("blah") :)
//style: b, i или u.
function output($txt,$style="font")
{
echo "
<{$style}>$txt</{$style}>
";
}

function output_heading($txt)
{
echo "<hr><font size='+1'><center>{$txt}</center></font><br><br>";
}

//вывод кода в рамочке
function output_showcode($code)
{

        echo "<p align='center'><font size='+1'>{$code}</font></p>";

}

function output_change_me($code,$id,$msg='Вы можете изменить свой гик-код вручную. Для этого внесите изменения в код ниже и нажмите "Сохранить"')
{
echo "
<center>
${msg}<br>
<script>
function subm()
{

if (document.input.gcode.value=='') {
    if (window.confirm('Сохранить в базе пустой код?'))

   {
document.input.gcode.value = escape(document.input.gcode.value);
while (document.input.gcode.value.indexOf('+') != -1) document.input.gcode.value=document.input.gcode.value.replace('+','%2B');
document.input.submit();
   }
                                    }
   else
   {
document.input.gcode.value = escape(document.input.gcode.value);
while (document.input.gcode.value.indexOf('+') != -1) document.input.gcode.value=document.input.gcode.value.replace('+','%2B');
document.input.submit();
   }

}
</script>

        <form name='input' action='index.php' method='get'>

        <textarea name=\"gcode\" rows=5 cols=35 wrap=\"on\">{$code}</textarea>
        <input name='act' type='hidden' value='portal'>
        <input name='site' type='hidden' value='22'>
        <input name='id' type='hidden' value={$id}>
        <input type='button' onclick=\"if (window.confirm('Вы уверены?')) subm();\" value='Сохранить'>
        </form>\n
        </center>
        ";
}

function output_gen_me($msg="Вы можете воспользоваться генератором гик-кода для изменения своего кода:")
{
echo "
<center><br><br>{$msg}<br><br>
<b><a href='index.php?act=portal&site=19'>Генератор</a></b></center>
";
}

function output_decode_me($id)
{
echo "
<center>
Вы можете проверить правильность своего кода, расшифровав его:<br><br>
<b><a href='index.php?act=portal&site=17&id={$id}'>Расшифровать</a></b></center>
";
}

function output_link($id,$code)
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
<textarea name='code1' rows=4 cols=50 wrap='on'><a href='http://www.geekclub.ru/decode.php?id={$id}'>{$code}</a></textarea>
</td>
<td align='center'>
<a href='http://www.geekclub.ru/decode.php?id={$id}'>{$code}</a>
<br>
<input type=\"button\" value=\"Копировать\" onclick=\"code1.select(); document.execCommand('copy');\">
</td>
</tr>
<tr>
<td><b>BB code:</b><br>
<textarea name='code2' rows=4 cols=50 wrap='on'>[URL=http://www.geekclub.ru/decode.php?id={$id}]{$code}[/URL]</textarea>
</td>
<td align='center'>
<a href='http://www.geekclub.ru/decode.php?id={$id}'>{$code}</a>
<br>
<input type=\"button\" value=\"Копировать\" onclick=\"code2.select(); document.execCommand('copy');\">
</td>
</tr>
</table>
</center>";
}


//сообщение с отказом гостям
function output_noguests()
{
echo "
<b>Гости не имеют доступа к панели управления гик-кодом. Зарегистрируйтесь!</b>
";
}

function output_epigraph()
{
echo "
<span align='right'><i><b>So you think you are a geek, eh?</b>
<br> - R. Hayden</i>
</span>
";
}

//приветственное сообщение
function output_welcome()
{
echo "
<b><center>Добро пожаловать в вашу панель управления гик-кодом! С её помощи вы сможете быстро и удобно сохранить свой гик-код в базе данных GeekClub, изменить свой код, сгенерировать ссылки на расшифровку своего кода.</b><br><br>
Принимаются абсолютно все предложения, баг-репорты и просто пожелания - оставляйте своё мнение о панели управления, системе гик-кода и о самом коде на форуме. Важно мнение любого!</center>
";
}



}

$cp = new cpanel;
$cp->init();
$cp->main();

?>