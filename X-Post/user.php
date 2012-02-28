<?php

class user
{
   function user()
   {
      global $mysql;
      
      //cookie or session?
      if (isset($_SESSION['uid']))
      {
          $mysql->connect();
          $q=$mysql->query("SELECT * FROM users WHERE uid = '".$_SESSION['uid']."'");
          $q = mysql_fetch_assoc($q);
          
	  if ($_SESSION['pass_hash'] == $q['pass'])
          {
            foreach ($q as $key=>$value)
	    {
	      $this->$key=$value;
	    }
	    if ($this->uid == '1') $this->is_admin = TRUE;
	    $this->update_session();
	    return;

          }
            
      }
      if (isset($_COOKIE['uid']))
      {
      	  $mysql->connect();
          $q=$mysql->query("SELECT * FROM users WHERE uid = '".$_COOKIE['uid']."'");
          $q = mysql_fetch_assoc($q);
	  if ($_COOKIE['pass_hash'] == $q['pass'])
          {
            foreach ($q as $key=>$value)
	    {
	      $this->$key=$value;
	    }
	    if ($this->uid == '1') $this->is_admin = TRUE;
	    $this->update_session();
	    return;

          }
      }
      $this->update_session();
      
    }
    
    function update_session()
    {
    	global $mysql,$cfg;
	$mysql->connect();
	
	$ip = $_SERVER['REMOTE_ADDR'];
	
	
	//do we have uid?
	if ($this->uid!=0) 
	{
	$mysql->query("DELETE FROM sessions WHERE `ip`='{$ip}' AND NOT(`uid`='{$this->uid}')");
	$q=$mysql->query("SELECT * FROM sessions WHERE `uid`='{$this->uid}'");
	
	if (@mysql_num_rows($q)!=0)
	{
	
	$mysql->query("UPDATE sessions SET `time` = '".time()."',`user`='{$this->name}',`ip`='{$ip}' WHERE `uid`='{$this->uid}'");
	} else 
	$mysql->query("INSERT INTO sessions VALUES ('{$this->uid}','".time()."','{$ip}','{$this->name}')");
	} else
	//ip
	{
	$q=$mysql->query("SELECT * FROM sessions WHERE `ip`='{$ip}'");
	
	if (@mysql_num_rows($q)!=0)
	{
	$mysql->query("UPDATE sessions SET `time` = '".time()."',`user`='{$this->name}',`uid`='{$this->uid}' WHERE `ip`='{$ip}'");
	} else 
	$mysql->query("INSERT INTO sessions VALUES ('{$this->uid}','".time()."','{$ip}','{$this->name}')");
	}
	//kill old sessions
	$mysql->query("DELETE FROM sessions WHERE `time`<'".(time()-($cfg['online_timeout']*60))."'");
	
	//echo "DELETE FROM sessions WHERE `time`<'".(time()-($cfg['online_timeout']*60))."'";
	
    }
   
}
$user = new user;
?>