<?php

class profile
{

     function run()
     {
        global $std,$user;
        switch ($std->input['opcode'])
        {
           case 'do_edit': $this->edit_profile(); continue;
           case 'edit': $this->edit_form(); continue;
           default: 
           case 'view': $this->view_profile(); continue;
        }
        $this->finish();
        
     }
     
     function view_profile()
     {
        global $std,$mysql,$skin,$user,$cfg;
        if (!$std->input['uid'] && !$user->uid)
        {
           $this->html.=$std->error('no_input');
           return;
        }
        if ($std->input['uid']) $uid = $std->input['uid']; else $uid = $user->uid;
        $mysql->connect();
        $q = $mysql->query("SELECT * FROM users WHERE uid = '".$uid."'");
        $data = mysql_fetch_assoc($q);
        $std->title = "Просмотр профиля пользователя ".$data['name'];
        $data['mail'] = $skin->mail($std->parse_email($data['mail']),$std->parse_email_link($data['mail']));
        
        $data['photo'] = $skin->photo($data['photo_url']);
        
        $data['www'] = $skin->www_link($data['www']);
        
        $data['geekcode'] = $skin->geekcode_link($data['geekcode'],rawurlencode($data['geekcode']));
        
        if ($data['icq']==0) $data['icq'] ='';
        if ($data['icq']) $data['icq_status'] = $skin->icq_status($data['icq']);
        if ($data['birthday']) $data['birthday'] = date($cfg['birth_dateformat'],$data['birthday']);
        $data['joined'] = date($cfg['dateformat'],$data['joined']);
        $this->html.=$skin->profile_view($data);
        
        if ($data['uid'] == $user->uid)
        {
           $this->html.=$skin->edit_profile_link($data['uid']);
        }
        
     }
     
     function edit_form()
     {
        global $std,$mysql,$skin,$user,$cfg;
        if (!$std->input['uid'])
        {
           $this->html.=$std->error('no_input');
           return;
        }
        if (($std->input['uid'] != $user->uid) AND (!$user->is_admin))
        {
           $this->html.=$std->error('no_rights');
           return;
        }
        
        $mysql->connect();
        $q = $mysql->query("SELECT * FROM users WHERE uid = '".$std->input['uid']."'");
        $data = mysql_fetch_assoc($q);
        $std->title = "Редактирование профиля пользователя ".$data['name'];
        //$data = $std->decode_entities($data);
        if ($user->is_admin) $data['old_pass'] = 'НЕ ТРЕБУЕТСЯ';
        if ($data['icq']==0) $data['icq'] ='';
        if ($data['photo']) $data['photo'] = $skin->photo($data['photo_url']);
        if ($data['birthday']) $data['birthday'] = date($cfg['birth_dateformat'],$data['birthday']);
        $this->html.=$skin->edit_profile($data,$cfg);
        
        
     }
     
     function edit_profile()
     {
        global $std,$mysql,$skin,$user,$cfg;
        if (!$std->input['uid'])
        {
           $this->html.=$std->error('no_input');
           return;
        }
        if (($std->input['uid'] != $user->uid) AND (!$user->is_admin))
        {
           $this->html.=$std->error('no_rights');
           return;
        }
        $std->input['uid'] = intval($std->input['uid']);
        $mysql->connect();
        
        $q = $mysql->query("SELECT * FROM users WHERE uid ='".$std->input['uid']."'");
        $q = mysql_fetch_assoc($q);
        
        if ((md5(md5($std->input['old_pass'])) != $q['pass']) AND !$user->is_admin)
        {
           $this->html.=$std->error('bad_pass');
           return;
        }
        $std->title = "Редактирование профиля пользователя";
        //начинаем копать входные переменные
        $www = $std->input['www'];
        if ($www AND ((!strstr($www,'http://')) OR (strstr($www,'javascript:'))))
        {
           $this->html.=$std->error('bad_www_url');
           return;
        }
        
        $uid = intval($std->input['uid']);
        
        $icq = intval($std->input['icq']);
        
        $birthday = $std->input['birthday'];

        if ($birthday and !preg_match("/\d{1,2}\.\d{1,2}\.\d{4,4}/",$birthday))
        {
          $this->html.=$std->error('shitty_birthday');
          return; 
        }
        $nums = explode(".",$birthday);
        $birthday = mktime(12,14,23,$nums[1],$nums[0],$nums[2]);

        $photo = $q['photo_url'];
        
        $email = $std->input['mail'];
        if (!preg_match("/.+@.+\.+/",$email))
        {
           $this->html.=$std->error('bad_mail');
           return;           
        }
        
        $geekcode = $std->input['geekcode'];
        
        //новый пасс?
        $new_pass = $std->input['new_pass'];
        if ($new_pass)   
        { 
        
        if (strlen($new_pass)>32)
        {
           $this->html.=$std->error('long_pass');
           return;
        }
        
        if (!preg_match("/[A-Za-z0-9~!@#$%^&*()_]+/",$new_pass))
        {
           $this->html.=$std->error('bad_pass');
           return;
        }
          
           $new_pass_hash = md5(md5($new_pass));
        } elseif (!$user->is_admin) {
          $new_pass_hash = md5(md5($std->input['old_pass']));
          } else {
            $new_pass_hash = $q['pass']; 
          }
        
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
        if (!((!strstr($photo,'http://')) OR (strstr($photo,'javascript:')) OR (!preg_match("/.+\.[jpg|jpeg|gif|png]/i",$photo)) OR (!exif_imagetype($photo)))) $photo_info = getimagesize($photo);
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
        
        
        
        }
        
        //спихнули файл
        if ($_FILES['photo_file']['name'])
        {
         $photo=$_FILES['photo_file'];
	
	$types = explode("|",$cfg['photo_upload_extensions']);
	$ext = preg_replace("/.+\.(.+)/","$1",$photo['name']);
	
	        
	if (!in_array($ext,$types))
	{
			$this->html.=$std->error('bad_photo');
		return;
	}
        if ($photo['size']>($cfg['max_photo_size']*1024))
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
        
        //translit rulit!
	$filename = $photo['name'];
	$filename = $user->uid.'_photo.'.$ext;
	
	move_uploaded_file($photo['tmp_name'],$cfg['upload_dir'].'/'.$filename);
	
	$photo = "http://".$_SERVER['HTTP_HOST']."/".$cfg['upload_dir']."/".$filename;
        	
	           
        }
        
        
        $mysql->connect();
        $q = $mysql->query("UPDATE users SET `www`='{$www}',`mail`='{$email}',`icq`='{$icq}',`photo_url`='{$photo}',`geekcode`='{$geekcode}',`pass`='{$new_pass_hash}',`birthday`='{$birthday}' WHERE `uid`='{$uid}'"); 
        
        $this->html.=$std->success('edited_profile',1,1);
           
     }
     
     function finish()
     {
        global $skin;
	$this->html=$skin->module($this->html);
     }
   
}


?>
