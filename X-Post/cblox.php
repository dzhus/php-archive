<?php

class cblox 
{
   function run()
   {
      global $user,$skin,$std,$mysql,$html;
      $q=$mysql->query("SELECT * FROM cblox");
      while ($data=mysql_fetch_assoc($q))
      {
	      $data['content']=html_entity_decode($data['content'],ENT_NOQUOTES); 
	      $block=$skin->block($data);
	      
	      $html["custom_{$data['name']}"] = $block;
      }
   }
}

?>
