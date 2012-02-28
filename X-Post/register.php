<?php

class reg
{
   function run()
   {
      global $cfg,$skin,$std;
      switch ($std->input['opcode'])
      {
         default:
         case 'reg_form': $this->reg_form(); continue;
         case 'register': $this->do_reg(); continue;
      }
      
      $this->finish();
   }
   
   function reg_form()
   {
      global $skin,$cfg,$user;
      /*if ($user->uid)
      {
         $this->html.=$std->error('already_registered');
         return;
      } */
      $this->html.=$skin->reg_form($cfg);
      $std->title = "Регистрация";
   }
   
   function do_reg()
   {
        global $std,$mysql,$skin,$user,$cfg,$mail;
        if ($user->uid)
        {
           $this->html.=$std->error('already_registered');
           return;
        }
        $mysql->connect();

/*        if (isset($_COOKIE['registered']))
        {
           $this->html.=$std->error('no_flood_regs');
           return;
        }*/
        
        $q = $mysql->query("SELECT * FROM users");
        while ($row = mysql_fetch_assoc($q))
        {
           $names = $row['name'];
           $mails = $row['mail'];
        }
        settype($names,'array');
        settype($mails,'array');
        
        $std->title = "Регистрация";
        //начинаем копать входные переменные
        $name = $std->input['name'];
        if (!$name)
        {
           $this->html.=$std->error('no_name');
           return;
        }

        if (stristr(join(",",$names),$name))
        {
           $this->html.=$std->error('name_in_use');
           return;
        }

        if (strlen($name)>20)
        {
           $this->html.=$std->error('long_name');
           return;
        }
        
        $pass = $std->input['pass'];
        
        if (!$pass)
        {
           $this->html.=$std->error('no_pass');
           return;

        }
        
        if (strlen($pass)>32)
        {
           $this->html.=$std->error('long_pass');
           return;
        }
        
        if (!preg_match("/[A-Za-z0-9~!@#$%^&*()_]+/",$pass))
        {
           $this->html.=$std->error('bad_pass');
           return;
        }
        
        $pass = md5(md5($pass));
        
        $www = $std->input['www'];
        if ($www AND ((!strstr($www,'http://')) OR (strstr($www,'javascript:'))))
        {
           $this->html.=$std->error('bad_www_url');
           return;
        }
        
        $icq = intval($std->input['icq']);
        
        $birthday = $std->input['birthday'];

        if ($birthday and !preg_match("/\d{1,2}\.\d{1,2}\.\d{4,4}/",$birthday))
        {
          $this->html.=$std->error('shitty_birthday');
          return; 
        }
        $nums = explode(".",$birthday);
        $birthday = mktime(12,14,23,$nums[1],$nums[0],$nums[2]);
        
        $email = $std->input['mail'];
        if (!preg_match("/.+@.+\.+/",$email))
        {
           $this->html.=$std->error('bad_mail');
           return;           
        }
        
        $geekcode = $std->input['geekcode'];
        
        //переходим к фотке
        if ($_FILES['photo_file']['name'] AND $std->input['photo_url'])
        {
           $this->html.=$std->error('no_both_photo');
           return;           
        }
        
        //дали URL
        if ($std->input['photo_url'])
        {
        $photo=$std->input['photo_url'];   
        if ((!strstr($photo,'http://')) OR (strstr($photo,'javascript:')) OR (!preg_match("/.+\.[jpg|jpeg|gif|png]/i",$photo)) OR (@exif_imagetype($photo))) $photo_info = getimagesize($photo);
        else
        {
            $this->html.=$std->error('bad_photo_url');
            return;
        }

        if (($photo_info[0] > $cfg['max_photo_w']) OR ($photo_info[1] > $cfg['max_photo_h']))
        {
            $this->html.=$std->error('bad_photo_size');
            return;
        }
        
        $photo = $std->input['photo_url'];
        
        }
        
        //спихнули файл
        if ($_FILES['photo_file']['name'])
        {
         $photo=$_FILES['photo_file'];
       $img_types = array (
       "image/gif",
       "image/jpeg",
       "image/png"       
       );
       
        if (!in_array($photo['type'],$img_types))
        {
            $this->html.=$std->error('bad_photo');
            return;
        }

        if ($photo['size']>$cfg['max_photo_size'])
        {
           $this->html.=$std->error('bad_photo_file_size');
           return;
        }
         
         
        $photo_info = getimagesize($photo['tmp_name']);
        
        if (($photo_info[0] > $cfg['max_photo_w']) OR ($photo_info[1] > $cfg['max_photo_h']))
        {
            $this->html.=$std->error('bad_photo_size');
            return;
        }
        
        $filename = preg_replace("/.+\.([gif|jpg|jpeg|png])/i",$cfg['upload_dir']."/".$uid."_photo.$1",$photo['name']);
        move_uploaded_file($photo['tmp_name'],$filename);
        $photo = "http://".$_SERVER['HTTP_HOST']."/".$filename;
                   
        }
        
        $joined = time();
        
        
        $mysql->connect();
        $q = $mysql->query("INSERT INTO users (`name`,`pass`,`www`,`mail`,`icq`,`geekcode`,`birthday`,`joined`,`photo_url`) VALUES ('{$name}','{$pass}','{$www}','{$email}','{$icq}','{$geekcode}','{$birthday}','{$joined}','{$photo}')"); 
        $new_uid = mysql_insert_id();
        $mail->sendmail('registered',$new_uid);
        $mail->sendmail('registered_admin',1);
        $this->html.=$std->success('user_registered',1);
        
        SetCookie('registered','1',time()+60*60*24*365);
     
   }
   
   function finish()
   {
      global $skin;
      $this->html=$skin->module($this->html);
   }
}


?>
