<?php

class skin_head
{
   function heading($title)
   {
      return "
      <html>
      <head>
      <title>{$title}</title>
      <link rel='stylesheet' href='<#IMG_DIR#>/main.css'>
      <meta http-equiv='Content-Type' content='text/html; charset=windows-1251'>
      </head>
      <body>
      <div class='logo'><center><a href='index.php'><img src='<#IMG_DIR#>/logo.jpg'></a></center></div>
      ";
   }
}

?>