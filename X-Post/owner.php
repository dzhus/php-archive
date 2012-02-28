<?php

class ownerbox
{
   function run()
   {
      global $skin,$mysql,$std,$cfg;
      
      $mysql->connect();
      $q = $mysql->query("SELECT * FROM users WHERE uid = '1'");
      
      $owner = mysql_fetch_assoc($q);
      
      $owner['mail'] = $skin->mail($std->parse_email($owner['mail']),$std->parse_email_link($owner['mail']));
      
      $photo_info = getimagesize($owner['photo_url']);
      $w = $photo_info[0];
      $h = $photo_info[1];
      if ($w > $cfg['owner_box_photo_w'] || $h > $cfg['owner_box_photo_h'])
      {
      
      if ($w >= $h)
      {
      $w = $cfg['owner_box_photo_w'];
      $w = "width='{$w}'";
      } 
      else 
      {
      $h = $cfg['owner_box_photo_h'];
      $h = "height='{$h}'";
      }
}
      
      $owner['photo'] = $skin->photo($owner['photo_url'],$w,$h);
      $owner['epoch'] = $skin->epoch_counter($owner['name'],time() - $owner['birthday']);
      
      $this->html.=$skin->owner_box($owner);
      
      $this->html=$skin->module($this->html);
   }
}

?>
