<?php

class admin
{
	function run()
	{
	global $user,$std;
	if (!$user->is_admin)
	{
		$this->html.=$std->error('no_rights');
		$this->finish(1);
		return;
	}
	$std->title = "Панель управления";
	switch ($std->input['opcode'])
	{
		default:
		case 'idx': $this->idx_page(); break;
		
		case 'settings' : $this->settings(); break;
		case 'do_settings' : $this->do_settings(); break;
		
		case 'cats': $this->cats(); break;
		case 'edit_cat_form': $this->edit_cat_form(); break;
		case 'add_cat_form':$this->add_cat_form();break;
		case 'do_add_cat':$this->do_add_cat();break;
		case 'do_edit_cat':$this->do_edit_cat(); break;
		
		case 'keywords': $this->keywords(); break;
		case 'delete_keyword':$this->delete_keyword();break;
		
		case 'moods': $this->moods(); break;
		case 'delete_mood':$this->delete_mood();break;
		
		case 'users': $this->users(); break;
		case 'delete_user' : $this->delete_user(); break;
		case 'approve_user' : $this->approve_user(); break;
		
		case 'files': $this->files();break;
		case 'add_file': $this->add_file_form();break;
		case 'do_add_file': $this->do_add_file();break;
		case 'edit_file': $this->edit_file_form();break;
		case 'do_edit_file': $this->do_edit_file();break;
		case 'delete_file':$this->delete_file();break;
			
		case 'cblox': $this->cblox();break;
		case 'add_cblock': $this->add_cblock_form();break;
		case 'do_add_cblock': $this->do_add_cblock();break;
		case 'edit_cblock': $this->edit_cblock_form();break;
		case 'do_edit_cblock': $this->do_edit_cblock();break;

		case 'wrappers': $this->wrappers();break;
		case 'edit_wrapper': $this->edit_wrapper();break;
		case 'do_edit_wrapper': $this->do_edit_wrapper();break;

	}
	
	$this->finish();
	}
	
	function idx_page()
	{
	global $skin;
	$this->html.=$skin->idx_page();
	}
	
	function settings()
	{
	global $skin;
	
	
	}
	
	
	function cats()
	{
	global $skin,$std,$cfg,$mysql;
	
	$mysql->connect();
	$q = $mysql->query("SELECT * FROM cats ORDER BY catname ASC");
	if ($q)
	{
		while ($row = mysql_fetch_assoc($q))
		{
		if ($row['caticon']) $row['caticon'] = $skin->icon($row['caticon']); else $row['caticon'] = "---";
		$cats[] = $skin->cats_row($row);
		}
	$this->html.=$skin->cats_list(join("",$cats));
	}
	
	}
	
	function add_cat_form()
	{
	global $skin;
	
	$this->html.=$skin->add_cat_form();
	}
	
	function edit_cat_form()
	{
	global $std,$skin,$mysql;
	
	if (!$std->input['cid'])
	{
		$this->html.=$std->error('no_input');
		return;
	}
	
	$mysql->connect();
	$cid = intval($std->input['cid']);
	
	$q = $mysql->query("SELECT * FROM cats WHERE cid = ".$cid);
	$data = mysql_fetch_assoc($q);
	$data['cats_list'] = $this->cats_list_selector($cid);
	$this->html.=$skin->edit_cat_form($data);
	
	
	}
	
	function do_add_cat()
	{
	global $std,$skin,$mysql;
		
	$mysql->connect();
	
	
	$catname = $std->input['catname'];
	if (!$catname)
	{
		$this->html.=$std->error('no_catname');
		return;
	}
	if ($std->input['color']) $color = $std->input['color']; else $color = "#".dechex(rand(180,256)).dechex(rand(180,256)).dechex(rand(180,256));
	$icon = $std->input['icon'];
	$q = $mysql->query("INSERT INTO cats (`catname`,`caticon`,`color`) VALUE ('{$catname}','{$icon}','{$color}')");
	$this->html.=$std->success("cat_added");
	$this->cats_list();     
	}
	
	function do_edit_cat()
	{
	global $std,$skin,$mysql;
	
	if (!$std->input['cid'])
	{
		$this->html.=$std->error('no_input');
		return;
	}
	
	$cid = intval($std->input['cid']);
	
	$mysql->connect();
	
	if ($std->input['delete'])
	{
		$new_cat = intval($std->input['cat_sel']);
		
		if (!$new_cat)
		{
		$this->html.=$std->error('no_input');
		return;
		}
		
			$q = $mysql->query("DELETE FROM cats WHERE cid = '".$cid."' LIMIT 1");
			$q = $mysql->query("UPDATE posts SET cat='".$new_cat."' WHERE cat = '".$cid."';");
			$this->html.=$std->success("cat_deleted");
		
	} else
	{
	
		$catname = $std->input['catname'];
		if (!$catname)
		{
		$this->html.=$std->error('no_catname');
		return;
		}
		if ($std->input['color']) $color = $std->input['color']; else $color = "#".dechex(rand(200,256)).dechex(rand(200,256)).dechex(rand(200,256));    
		$icon = $std->input['icon'];
		$q = $mysql->query("UPDATE cats SET `catname`='{$catname}',`caticon`='{$icon}',`color`='{$color}' WHERE cid='{$cid}'");
		$this->html.=$std->success("cat_edited");
	}
	$this->cats_list();  
	}
	
	function keywords()
	{
	global $skin,$std,$cfg,$mysql;
	
	$mysql->connect();
	$q = $mysql->query("SELECT * FROM keywords");
	if ($q)
	{
	while ($row = mysql_fetch_assoc($q))
	{
		$keywords[]= $skin->keywords_row($row);
	}
	$this->html .= $skin->keywords_list(join("",$keywords));
	}  
	
	if (mysql_num_rows($q)==0)
	{
		$this->html.=$skin->no_keywords();
		return;
	}
	
	}
	
	function delete_keyword()
	{
	global $mysql,$std;
	$mysql->connect();
	if (!$std->input['keyword'])
	{
		$this->html.=$std->error('no_input');
		return;
	} else $keyword = $std->input['keyword'];
	$q = $mysql->query("DELETE FROM keywords WHERE `keyword` = '{$keyword}' LIMIT 1");
	$this->html.=$std->success("keyword_deleted");
	$this->html.=$this->keywords();
	}
	
		
	function files()
	{
	global $skin,$mysql,$cfg;	
	$mysql->connect();
	$q = $mysql->query("SELECT * FROM files ORDER BY fid ASC");
	
	$this->html.=$skin->add_file_link();
	
	if (mysql_num_rows($q)==0)
	{
		$this->html.=$skin->no_files();
		
	} else
	{
	while ($row = mysql_fetch_assoc($q))
	{
			$row['date'] = date($cfg['dateformat'],$row['date']);
			$row['link'] = $cfg['upload_dir'].'/'.$row['file'];
			if ($row['vip']) $row['vip_note']='<br><i>Только для друзей!</i>';
			$files[] = $skin->file_row($row);
	}
	$this->html.=$skin->files_list(@implode("",$files));
	}
	$this->html.=$skin->add_file_link();
	
	
	
	}
	
	function add_file_form()
	{
	global $skin,$cfg;
	$this->html.=$skin->add_file_form($cfg);
	}



	function edit_file_form()
	{
	global $std,$skin,$mysql;
	
	$fid = intval($std->input['fid']);
	
	if (!$fid)
	{
		$this->html.=$std->error('no_input');
		return;
	}
	
	$mysql->connect();
	$q = $mysql->query("SELECT * FROM files WHERE `fid`={$fid}");
	$data = mysql_fetch_assoc($q);
	($data['vip']) ? $data['sel'] = 'checked' : $data['sel'] ='';
	$this->html.=$skin->edit_file_form($data);
	}

	function do_edit_file()
	{
	global $mysql,$std;
	$fid = intval($std->input['fid']);
	
	if (!$fid)
	{
		$this->html.=$std->error('no_input');
		return;
	}
	
	$desc = strval($std->input['desc']);
	$vip = intval($std->input['vip']);
	
	$mysql->connect();
	$q = $mysql->query("UPDATE files SET `desc`='{$desc}',`vip`='{$vip}' WHERE `fid`={$fid} LIMIT 1");
	$this->html.=$std->success('file_edited');
	$this->files();
	}

	function delete_file()
	{
	global $mysql,$std,$cfg;
	
	$fid = intval($std->input['fid']);
	$fname = strval($std->input['fname']);
	
	if (!$fid)
	{
		$this->html.=$std->error('no_input');
		return;
	}
	
	$mysql->connect();
	$q = $mysql->query("DELETE FROM files WHERE `fid`={$fid}");
	
	$this->html.=$std->success('file_deleted');
	$this->files();
	unlink($fname);
	
	}

	//handle new file upload
	function do_add_file()
	{
	global $mysql,$skin,$cfg,$std;
	
	$file = $_FILES['file'];

	//filesize check
	if ($file['size']>$cfg['upload_max_size']*1024)
	{
		$this->html.=$std->error('bad_file_size');
		return;
	}
	//type check
	$types = explode("|",$cfg['upload_extensions']);
	$ext = preg_replace("/.+\.(.+)/","$1",$file['name']);
	
	if (!in_array($ext,$types))
	{
			$this->html.=$std->error('bad_file');
		return;
	}
	
	$desc = strval($std->input['desc']);
	
	$mysql->connect();
	
	//get a brand new fid for file
	$q = $mysql->query("SELECT * FROM files ORDER BY fid DESC");
	$last = mysql_fetch_assoc($q);
	$new_fid = $last['fid']+1;
	
	//translit rulit!
	$filename = $std->transliterate($file['name']);
	$filename = $new_fid.'_'.$filename;
	
	$vip = intval($std->input['vip']);
	
	$q = $mysql->query("INSERT INTO files (`date`,`desc`,`file`,`vip`) VALUES ('".time()."','{$desc}','{$filename}','{$vip}');");
	
	//save file on disk:
	move_uploaded_file($file['tmp_name'],$cfg['upload_dir'].'/'.$filename);
	
	
	$this->html.=$std->success('file_added');
	
	$this->files();
	}

	function moods()
	{
	global $skin,$std,$cfg,$mysql;
	
	$mysql->connect();
	$q = $mysql->query("SELECT * FROM moods ORDER BY moodname ASC");
	
	if (mysql_num_rows($q)==0)
	{
		$this->html.=$skin->no_moods();
		return;
	}
	
	while ($row = mysql_fetch_assoc($q))
	{
		$moods[]= $skin->moods_row($row['moodname']);
	}
	$this->html.= $skin->moods_list(join("",$moods));
	
	}

	function delete_mood()
	{
	global $skin,$std,$mysql;
	if (!$std->input['mood'])
	{
		$this->html.= $std->error('no_input');
		return;
	} else $mood = $std->input['mood'];
	$q = $mysql->query("DELETE FROM moods WHERE moodname = '{$mood}'");
	$this->html.= $std->success('mood_deleted');
	$this->html.= $this->moods();
	}

	function users()
	{
	global $skin,$mysql,$std,$cfg;
	$mysql->connect();
	$q = $mysql->query("SELECT * FROM users ORDER BY joined ASC");
	while ($row = mysql_fetch_assoc($q))
	{
		if ($row['uid']!=0) {
		if (!$row['approved']) $row['a_link'] = $skin->approval_link($row['uid'],$cfg['skin_dir'].'/'.$std->skin_read()); else $row['a_link'] = "---";
		$users[] = $skin->users_row($row,$row['uid']);
		}
	}
	$this->html .= $skin->users_list(join("",$users));
	}

	function delete_user()
	{
	global $mysql,$std;
	if (!$std->input['uid'])
	{
		$this->html.= $std->error('no_input');
		return;
	} else $uid = $std->input['uid'];
	if ($uid==0)
	{
		$this->html.= $std->error('no_rights');
		return;
	}
	$mysql->connect();
	$q = $mysql->query("DELETE FROM users WHERE uid = '{$uid}' LIMIT 1");
	$this->html.=$std->success("user_deleted",1,1);
	}

	function approve_user()
	{
	global $mysql,$std,$mail;
	if (!$std->input['uid'])
	{
		$this->html.= $std->error('no_input');
		return;
	} else $uid = $std->input['uid'];
	$mysql->connect();
	$q = $mysql->query("UPDATE users SET approved = 1 WHERE uid = '{$uid}'");
	$this->html .= $std->success("user_approved",0,1);
	}

	function cats_list_selector($ex='')
	{
	global $mysql,$skin;
	
	$q = $mysql->query("SELECT * FROM cats ORDER BY catname ASC");
	if ($q)
	{
		while ($row = mysql_fetch_assoc($q))
		{
		if ($row['cid']!=$ex) $data['cats'][] = $skin->cats_row_selector($row);
		}
	}
	if ($data['cats']) $data['cats'] = $skin->cats_list_selector(join("",$data['cats']));
	else $data['cats'] = $skin->cats_list_selector();
	return $data['cats'];
	}


	function cblox()
	{
	global $mysql,$skin;
	
	$q = $mysql->query("SELECT * FROM cblox");
	$count = mysql_num_rows($q);
	if (!$count)
	{
		$this->html.=$skin->no_cblox();
		$this->html.=$skin->add_cblock_link();
		return;
	}
	while ($row = mysql_fetch_assoc($q))
	{
		switch ($row['perm'])
		{
			case 0: $row['perm']='для всех'; break;
			case 1: $row['perm']='только для друзей'; break;
			case 2: $row['perm']='личное'; break;
		}
		
		$list[] = $skin->cblox_row($row);
	}
	$list = $skin->cblox_list(join("",$list));
	$this->html.=$list;
	$this->html.=$skin->add_cblock_link();
	}

	function add_cblock_form()
	{
	global $skin;
	
	$this->html.=$skin->add_cblock_form();
	
	}
	
	
	function do_add_cblock()
	{
	global $skin,$std,$mysql;

	if (!$std->input['name']||!$std->input['title']||!$std->input['content'])
	{
		$this->html.=$std->error('no_input');
		return;
	}
	
	$name=$std->input['name'];
	$title=$std->input['title'];
	$content=$std->input['content'];
	$content=html_entity_decode($content,ENT_QUOTES);
	$perm=$std->input['perm'];

	$q = $mysql->query("INSERT INTO cblox (`name`,`title`,`content`,`perm`) VALUES ('{$name}','{$title}','{$content}','{$perm}')");
	
	$this->html.=$std->success('cblock_added');
	
	}

	function edit_cblock_form()
	{
	global $skin,$std,$mysql;
	if (!$std->input['bid'])
	{
		$this->html.=$std->error('no_input');
		return;
	}
	$bid=$std->input['bid'];
	
	$q=$mysql->query("SELECT * FROM cblox WHERE `bid`='{$bid}'");
	
	$data = mysql_fetch_assoc($q);

	$data['perm'.$data['perm']]='checked';

	
	
	$this->html.=$skin->edit_cblock_form($data);
	
	}

	function do_edit_cblock()
	{
	global $mysql,$std;	
	if (!$std->input['bid'])
	{
		$this->html.=$std->error('no_input');
		return;
	}

	if (!$std->input['name']||!$std->input['title']||!$std->input['content'])
	{
		$this->html.=$std->error('no_input');
		return;
	}
	$bid=intval($std->input['bid']);
	$name=$std->input['name'];
	$title=$std->input['title'];
	$content=$std->input['content'];
	$perm=$std->input['perm'];
	$content=html_entity_decode($content,ENT_QUOTES);

	
	if ($std->input['delete_cblock']) 
	{
		$q=$mysql->query("DELETE FROM cblox WHERE bid='{$bid}'");
		$this->html.=$std->success('cblock_deleted');
		return;
	}
	
	$q = $mysql->query("UPDATE cblox SET`name`='{$name}',`title`='{$title}',`content`='{$content}',`perm`='{$perm}' WHERE bid='{$bid}'");
	$this->html.=$std->success('cblock_edited');


	}


	function wrappers()
	{
	global $skin,$cfg;
	
	print_r(scandir($cfg['skin_dir']));	
	}
	
	function edit_wrapper()
	{
	
	global $std,$skin,$cfg;
	if (!$std->input['skin'])
	{
		$this->html.=$std->error('no_input');
		return;
	}

	$selected_skin=$std->input['skin'];

	$f=join("",file($cfg['skin_dir'].'/'.$selected_skin.'/wrapper.html'));
	$f=str_replace(array('{','}'),array('{%','%}'),$f);
	
	$blocklist=$this->blocklist();
	
	$this->html.=$skin->edit_wrapper_form($f,$selected_skin,$blocklist);


	
	}

	function do_edit_wrapper()
	{
	global $std,$skin,$cfg;
	
	if (!$std->input['content']||!$std->input['skin'])
	{
		$this->html.=$std->error('no_input');
		return;
	}

	$selected_skin=$std->input['skin'];
	
	$content = $std->input['content'];
	echo $content;
	//проверка наличия критических подстановок в шаблоне:
	$crucial=array('main','userbox');
	foreach ($crucial as $part)
	{
		if (!strstr($content,'{%'.$part.'%}'))
		{
			$this->html.=$std->error('crucial_parts_missing');
			return;
		}
	}

	$content=str_replace(array('{%','%}'),array('{','}'),$content);
	
	$content=html_entity_decode($content,ENT_QUOTES);
	//kill those nasty экранированные кавычки
        $content=str_replace("\'","'",$content);
	$content=str_replace('\"','"',$content);

	
	$f=fopen($cfg['skin_dir'].'/'.$selected_skin.'/wrapper.html','w');
	fwrite($f,$content,strlen($content));
	fclose($f);
	
	}
	
	function blocklist()
	{
	global $mysql,$skin;

	include("side_modlist.php");
	
	$blocks['main']=array('desc'=>'Основная часть');

		
	foreach($side_modlist as $mod=>$val)
	{
		$blocks[$mod]=$val;
	}


	$q=$mysql->query("SELECT * FROM cblox");
	
	while ($row=mysql_fetch_assoc($q))
	{
		if (($row['name'])!='cblox')
		$blocks['custom_'.$row['name']]=array 
					(
					'desc'=>$row['title']
					);
	}
	
	foreach ($blocks as $name=>$stuff)
	{
	$blocks_list[]=$skin->blocklist_row($name,$stuff);
	}

	return join("",$blocks_list);
	}
	
	
	//FINISH HIM!
	function finish($nowrapper=false)
	{
	global $skin;
	//nowrapper option for error screens...
	if ($nowrapper) $this->html=$skin->module($this->html); else $this->html=$skin->module($skin->wrapper($this->html));
	}
	
}

?>
