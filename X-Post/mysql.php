<?php

class mysql
{
   function connect()
   {
      global $cfg;
      mysql_connect($cfg['dbhost'],$cfg['dbuser'],$cfg['dbpass']);
      mysql_select_db($cfg['dbname']);
   }
   
   function disconnect()
   {
      mysql_close();
   }
   
   function query($q,$multi=0)
   {
      global $global_skin,$std;
      
      if ($multi)
      {
         $queries=explode(";",$q);
         
         foreach ($queries as $query)
         {
            if (strlen($query)>5) 
            {
	    $result[]=mysql_query($query);
	    
	    
            if (mysql_errno())
                {
                    echo $global_skin->error("Ошибка в запросе: ".$query."<br>".mysql_errno().": ".mysql_error());
                    return;
                }
		else $this->counter++;
            }
         }
      } else {
              if (strlen($q)>5) 
              {
	       $result=mysql_query($q);      
	       echo $query;
               if (mysql_errno())
               {
                  echo $global_skin->error("Ошибка в запросе: ".$q."<br>".mysql_errno().": ".mysql_error());
                  return;
               }
	       else $this->counter++;
              }
             }
      return $result;
      
   }
   
   function cache()
   {
   global $std;
   
   
   }
   
}

$mysql= new mysql;

?>