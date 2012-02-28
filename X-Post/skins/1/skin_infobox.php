<?php

class skin_infobox
{
   function infobox($data)
   {
      return "
      
      <div class='subheading'>
      Информация</div>
      {$data['blog_life']}<br>
      В <a href='index.php?a=sbox'>SBoxx</a> {$data['sbox']} мессаг<br>
      Всего {$data['posts']} записей<br>
      {$data['comments']} комментариев<br>
      В онлайне:&nbsp;{$data['online']}
      </div>";
   }
   
  
   function blog_life($days,$string)
   {
      return "
      Блогу уже {$days} {$string}
      ";
   }
   
   function module($data)
   {
      return "
      <div class='module'>
      {$data}
      </div>
      ";
   }
   
   function user_link($data)
   {
   return "<a href=\"user{$data['uid']}.html\">{$data['user']}</a>";
   }
   
   function guests($count)
   {
   return "гостей - {$count}";
   }

}

?>