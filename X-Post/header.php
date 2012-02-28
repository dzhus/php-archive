<?php

class head
{
   function run()
   {
      global $cfg,$std,$skin;
      $title = $cfg['blog_name']." &gt; ".$std->title;
      $this->html.=$skin->heading($title);
   }
}



?>
