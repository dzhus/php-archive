<?php

class skin_sbox_export
{


   function msg($data)
   {
   return "
   <div class='msgrow'>{$data['admin_links']}<span title='{$data['date']}'>[i]</span>{$data['author_link']}:&nbsp;{$data['text']}</div>
   ";

   }
   
   function msg_list($rows)
   {
   return "
   <html>
      <head>
      <title>Сообщения SpeakerBoxx</title>
      <link rel='stylesheet' href='<#IMG_DIR#>/main.css'>
      <meta http-equiv='Content-Type' content='text/html; charset=windows-1251'>
      </head>
      <body class>
   {$rows}
   </body>
   <script>
   setTimeout('location.reload()',15000);
   </script>
   </html>
   ";
   }
   
   function msg_list_arch($rows)
   {
   return "
   <html>
      <head>
      <title>Сообщения SpeakerBoxx</title>
      <link rel='stylesheet' href='<#IMG_DIR#>/main.css'>
      <meta http-equiv='Content-Type' content='text/html; charset=windows-1251'>
      </head>
      <body class>
   {$rows}
   </body>
   </html>
   ";
   }
   
   function admin_links($data)
   {
   return "
   <a href='#' onClick='if(window.confirm(\"Удалить запись?\")) parent.location.href=\"index.php?a=sbox&opcode=del&mid={$data['mid']}\";'><img src='<#IMG_DIR#>/delete.gif' title='Удалить' border=0></a>&nbsp;
   ";
   }
   
   function author_link($data)
   {
   return "<a href=\"user{$data['uid']}.html\" target='_blank'>{$data['name']}</a>";
   }
   
   function author_link_guest()
   {
   return "Гость";
   }
   
      function no_messages()
   {
   return "
   <div class='note'>В чате нет сообщений</div>
   ";
   }
   
      function divisor()
   {
   return "
   ";
    }

}

?>