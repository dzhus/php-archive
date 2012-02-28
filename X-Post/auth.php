<?php

class auth
{
   
   function run()
   {
      global $std,$user;
      switch ($std->input['opcode'])
      {
      case 'login': $this->login(); continue;
      case 'logout': $this->logout(); continue;
      default: if (!$user->uid) $this->login_form(); else $this->logout(); continue;
      }
      $this->finish();
   }
   
   function login_form()
   {
      global $global_skin,$std,$skin;
      $std->title = "Вход в систему";
      $this->html.=$skin->login_form();
      $this->html.=$global_skin->main_link();

   }
   

   function logout()
   {
      global $global_skin,$std,$skin;
      session_unset();
      SetCookie('uid','',time()+60*60*24*7);
      SetCookie('pass_hash','',time()+60*60*24*7);
      //unset($_COOKIE['pass_hash']);
      //unset($_SESSION['uid']);
      //unset($_SESSION['pass_hash']);
      
      $std->title = "Выход выполнен";
      $this->html.=$std->success('logout',1,0);
 
   }
   
   function login()
   {
      global $mysql,$std,$global_skin,$skin,$user;
      $mysql->connect();
      $name = str_replace("'","\'",strtolower($std->input['name'])); 
      $pass = $std->input['pass'];
      $q = $mysql->query("SELECT * FROM users WHERE lower(name) = '".$name."'");
      if ($q) 
      {
         
            $q = mysql_fetch_assoc($q);
            
            if (!$q['approved'])
            {
               $this->html.=$std->error('user_not_yet_approved');
               return;
            }
            
            if ($q['pass'] == md5(md5($pass)))
            {

	       $_SESSION['uid'] = $q['uid'];
	       $_SESSION['pass_hash'] = $q['pass'];
	       SetCookie('uid',$q['uid'],time()+60*60*24*7);
	       SetCookie('pass_hash',$q['pass'],time()+60*60*24*7);
	       foreach ($q as $key=>$value)
               {
                $user->$key = $value;
               }
               if ($user->uid == '1') $user->is_admin = TRUE;
               $std->title = "Вход выполнен";
               $this->html.=$std->success('login',1,0);
	       $mysql->connect();
            } else {
               $std->title = "Ошибка авторизации";
               $this->html.=$std->error('login_fail');
               $this->html.=$skin->login_form();
               }
      
      } else 
      {
         $std->title = "Ошибка авторизации";
         $this->html.=$std->error('login_fail');
         $this->html.=$skin->login_form();
         
      }
   }

   function finish()
   {
	   global $skin;
	   $this->html=$skin->module($this->html);
   }	   
}

?>
