<?php

class global_skin
{
   function error($msg)
   {
      return "
      <br /><br /><div class='error'>{$msg}</div><br /><br />
      ";
   }
   
   function status($msg)
   {
      return "
      <br /><br /><div class='status'>{$msg}</div><br /><br />
      ";
   }
   
   function main_link()
   {
      return "
      <br /><br /><div class='mainlink'><a href='index.php'>[Перейти на главную страницу]</a></div>
      ";
   }
   
   function back_link($link)
   {
      return "
      <br /><br /><div class='mainlink'><a href='{$link}'>[Назад]</a></div>
      ";
   }
   
   function vip_txt($text)
   {
      return "
      <div class='vip_text'>{$text}</div>
      ";
   }
   
   function vip_txt_hidden()
   {
      return "
      <div class='vip_text'>*<i><small>Текст, доступный только друзьям</small></i></div>
      ";
   }
   
   function cats_list($rows='')
   {
      return "
      <select name='cat'>
      {$rows}
      </select>
      ";
   }
   
   function cats_row($row)
   {
      return "
      <option value=\"{$row['cid']}\" style=\"background-color:{$row['color']}\" {$row['sel']}>{$row['catname']}</option>
      ";
   }
   
        

   function moods_list($rows='')
   {
      return "
      <select name='moods_list'>
      {$rows}
      </select>
      ";
   }
   
   function moods_row($row)
   {
      return "
      <option onClick=\"document.addentry.mood.value=moods_list.options[moods_list.selectedIndex].text;\">{$row['moodname']}</option>
      ";
   }
   
   function keywords_list($rows='')
   {
      return "
      <select name='keywords_list'>
      {$rows}
      </select>
      ";
   }
   
   function keywords_row($row)
   {
      return "
      <option  onClick=\"document.addentry.keywords.value=document.addentry.keywords.value+keywords_list.options[keywords_list.selectedIndex].text+','\" value=\"{$row['keyword']}\">{$row['keyword']}</option>
      ";
   }

}

$global_skin = new global_skin;

?>