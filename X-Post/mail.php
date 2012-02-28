<?php

class mail
{
   function sendmail($msg,$uid,$url)
   {
      global $cfg,$std,$mysql;
      require "mail_msg.php";
           
      $q = $mysql->query("SELECT * FROM users WHERE uid='{$uid}'");
      $userdata = mysql_fetch_assoc($q);
      $name = $userdata['name'];
      $mail = $userdata['mail'];
      
      $txt = $mail_messages[$msg];
      $txt = str_replace(array('{username}','{blog_name}','{blog_url}','{url}'),array($name,$cfg['blog_name'],$cfg['blog_url'],$url),$txt);
   
   mail($mail,$cfg['blog_name'],$txt,"X-mailer: X-Post Mailer\r\nFrom: ".$cfg['from_mail']);

      
   }
   
   
}

$mail = new mail;

?>