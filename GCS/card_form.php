<?php

require "cfg.php";  
require_once $this->SDK_PATH."ipbsdk_class.inc.php";
$SDK =& new IPBSDK();

$minfo = $SDK->get_advinfo();

if (!$minfo['id'])
{
   echo("<b>Ошибка!</b> Гости не имеют доступа к GCard. Зарегистрируйтесь и откройте для себя полные возможности GeekClub!");
   exit;
}

if (!$minfo['geekcode'])
{
   echo("Ваш гик-код не найден в базе данных! Воспользуйтесь <a href=http://cp.geekclub.ru>панелью управления</a> для сохранения гик-кода в базе данных.");
   exit;
}

?>
<form name="cardform" action="scripts/card.php" target="card" method="get">

<fieldset><legend><b>Основные настройки</b></legend>

<input name="id" type="hidden" value="<?php echo $minfo['id']; ?>">
<input name="card_opcode" type="hidden" value="10">
<input name="use_avatar" type="checkbox" value="1">&nbsp;Использовать аватар?<br>
<input name="use_email" type="checkbox" value="1">&nbsp;Выводить e-mail?<br>
<input name="use_icq" type="checkbox" value="1">&nbsp;Выводить ICQ?<br><br>
</fieldset>
<br>
<fieldset><legend><b>Настройки шрифта</b></legend>
<select size="1" name="font">
  <option value="courb">Courier Жирный</option>
  <option value="cour">Courier</option>
  <option value="mt">MicroTech</option>

  <option value="myr">MyriadPro</option> 
  <option value="agit">AgitProp</option> 
  <option value="lucid">Lucida Console</option>
  <option value="bauhs">Bauhaus 93</option>
  <option value="arvigo">Arvigo</option>
  </select>&nbsp;Шрифт<br><br>  
  </fieldset>
  
<fieldset><legend><b>Настройки подложки</b></legend>

<select size="1" name="bg">
  <option value="str1">Striped 1</option>
    <option value="plain1">Plain 1</option>
</select>&nbsp;Стиль фона<br><br>  

<select size="1" name="color">
  <option value="std">Стандарт</option>
    <option value="green">Зелёный</option>
      <option value="red">Красный</option>
        <option value="blue">Синий</option>
</select>&nbsp;Цвет<br><br>  

</fieldset>
  


<input type="submit" value="Сгенерировать GCard" onClick="cardform.card_opcode.value=10; cardform.submit();"><br>


<div align="center"><iframe src="" marginwidth=0 marginheight=0 width="300" height="188" name="card"></iframe><br><br>

<input type="button" value="Сохранить GCard" onClick="cardform.card_opcode.value=20; cardform.submit();">&nbsp;&nbsp;&nbsp;
<input type="button" value="Восстановить GCard" onClick="cardform.card_opcode.value=30; cardform.submit();"></div>

</form>