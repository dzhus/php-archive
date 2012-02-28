<?php

class skin_sbox
{

   function sbox_header()
   {
   return "
   <div class='subheading'>SpeakerBoxx</div><br>
   <div class='desc'>Болталка предназначена для обмена сообщениями между автором блога и читателями. Пожалуйста, соблюдайте правила приличия</div><br />
   ";
   }
   
   function sbox_form()
   {
   return "
   <center><form name=\"sbox\" action=\"index.php\" method=\"post\">
   <input type=\"hidden\" name=\"a\" value=\"sbox\">
   <input type=\"hidden\" name=\"opcode\" value=\"add\">
   <input type=\"text\" name=\"msg\" size=\"80\">&nbsp;&nbsp;<input type=\"submit\" value=\"Отправить\">
   </form>
   <b>Теги:</b><br>
   <a title=\"Курсив\" onClick='sbox.msg.value+=\"[i][\/i]\";'>[i][/i]</a>
   &nbsp;&nbsp;
   <a title=\"Жирный\" onClick='sbox.msg.value+=\"[b][\/b]\";'>[b][/b]</a>
   &nbsp;&nbsp;
   <a title=\"Подчёркнутый\" onClick='sbox.msg.value+=\"[u][\/u]\";'>[u][/u]</a>
   &nbsp;&nbsp;
   <a onClick='sbox.msg.value+=\"[em][\/em]\";'>[em][/em]</a>
   &nbsp;&nbsp;
   <a onClick='sbox.msg.value+=\"[big][\/big]\";'>[big][/big]</a>
   &nbsp;&nbsp;
   <a onClick='sbox.msg.value+=\"[small][\/small]\";'>[small][/small]</a><br>
   <small>Для просмотра информации о времени сообщения наведите курсор на [i]<br>Новые сообщения - выше, старые - ниже<br>Окно SpeakerBoxx автоматически обновляется каждые 15 секунд</small></center>
   ";
   }
   
   
   
   
   function status($shown)
   {
   return "
   <div class='note'>
   Показано {$shown} последних сообщений<br />[ <a href=\"index.php?a=sbox&opcode=all\">Просмотр всех сообщений</a> ]
   </div>
   ";
   }
   
      
   function status_viewall($shown)
   {
   return "
   <div class='note'>
   Показаны все сообщения чата - всего {$shown} сообщений<br />[ <a href=\"index.php?a=sbox\">Вернуться в обычный режим</a> ]
   </div>
   ";
   }
   
   function makeframe($type='')
   {
   return "
   <center><iframe src='index.php?a=sbox_export&opcode=view{$type}' width='95%' height='400px' name='sbox_messages'>Chilly stuff</iframe></center>
   ";
   }
   
   function makeframe_archive($type='')
   {
   return "
   <center><iframe src='index.php?a=sbox_export&opcode=view{$type}' width='95%' height='1000px' name='sbox_messages'>Hot stuff</iframe></center>
   ";
   }
   
   
   function module($data)
   {
      return "
      <div class='module'>{$data}</div>
      ";
   }
   

}

?>