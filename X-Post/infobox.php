<?php

class infobox
{
   function run()
   {
      global $mysql,$skin,$cfg;
      
      //создадим массив $data:
      
      $data['date'] = date($cfg['dateformat']);
      
      $mysql->connect();
     
      //всякие выборки и подсчёты
      //comments count
      $q = $mysql->query("SELECT * FROM comments");
      $data['comments'] = mysql_num_rows($q);
      
      //sbox shouts count
      $q = $mysql->query("SELECT * FROM sbox");
      $data['sbox'] = mysql_num_rows($q);
      
      //total posts count
      $q = $mysql->query("SELECT * FROM posts");
      $data['posts'] = mysql_num_rows($q);
      
      $q = mysql_fetch_assoc($q);

      //время жизни блога
      $blog_life = round((time() - $q['date']) / (60*60*24));
      
      switch (substr($blog_life,-1,1))
      {
         case 1: $str = 'день'; continue;
         case 2: 
         case 3:
         case 4: $str = 'дня'; continue;
         default: $str = 'дней'; continue;
      }
      if (substr($blog_life,0,1) == 1) $str = 'дней';
      
      $data['blog_life'] = $skin->blog_life($blog_life,$str);
      

      //пользователи онлайн
      $q = $mysql->query("SELECT * FROM sessions");
      
      while ($row = mysql_fetch_assoc($q))
      {
        
      	if ($row['uid']!=0) $users[]=$skin->user_link($row); else $guests++;
      }
      
      if ($users) $users = join(", ",$users);
      if ($guests) $users.='; ';
      if ($guests) $guests=$skin->guests($guests);
      $data['online']=$users.$guests;
          
      $this->html.=$skin->infobox($data);

      $this->html=$skin->module($this->html);
   }
}


?>
