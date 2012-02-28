<?php

class skin_sbox_last
{

   
   
   
   function msg($data)
   {
   return "
   <div class='msgrow_small'><span title='{$data['time']}'>[i]</span>{$data['author_link']}:&nbsp;{$data['text']}</div>
   ";

   }
   
   function msg_list($rows)
   {
   return "
   {$rows}
   ";
   }
   
   function module($data)
   {
      return "
      <div class='module'>
      <div class='subheading'>
      <span title='Новые сообщения - выше'>SpeakerBoxx</span></div>
      {$data}
      </div>

      ";
   }
   
   function divisor()
   {
   return "
   ";
   }
   
   function no_messages()
   {
   return "
   <div class='note'>Нет сообщений</div>
   ";
   }
   
   function author_link($data)
   {
   return "<a href=\"user{$data['uid']}.html\">{$data['name']}</a>";
   }
   
   function author_link_guest()
   {
   return "Гость";
   }

}


?>