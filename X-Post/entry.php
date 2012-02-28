<?php

class entry
{
   function run()
   {
     global $std,$skin,$user;
     
    
     switch ($std->input['opcode'])
     {
        case 'add_form': $this->add_form(); continue;
        case 'view':
        case 'viewentry': $this->view_entry();continue;
        case 'add': $this->add_entry(); continue;
        case 'edit_form': $this->edit_form(); continue;
        case 'edit': $this->edit_entry(); continue;
        case 'delete': $this->delete_entry(); continue;
        case 'add_comment': $this->do_add_comment(); break;
        case 'delete_comment': $this->delete_comment(); break;
        case 'approve_comment': $this->approve_comment(); break;
	case 'appr_comments': $this->view_comments(1); break;
        default:
        case 'all': $this->view_all_prepare(); $this->view_all(); continue;
	
     }
     $this->finish();
   }
  
   function view_all_prepare()
   {
      global $std,$cfg,$user;
	
	
	
	if ($std->input['offset']) $this->offset = intval($std->input['offset']); else $this->offset=0;
	if ($std->input['limit']) $this->limit = intval($std->input['limit']); else $this->limit=10;
	$this->mood=strval($std->input['mood']);
	$this->cat=intval($std->input['cat']);
	$this->keyword = strval($std->input['keyword']);
	$this->contents = strval($std->input['contents']);
		
	if ($user->is_admin != 1) $this->condition.= "WHERE `private` = 0"; else $this->condition = "WHERE `private` IN(0,1)";
	
	//сформируем условие для SQL-запроса и часть навигационных ссылок
	if ($this->mood) {
		$this->condition .=" AND `mood`='{$this->mood}'";
		$this->pars.="&mood={$this->mood}";
			}
	
	if ($this->keyword) {
		$this->condition .= " AND `keywords` LIKE '%{$this->keyword}%'";
		$this->pars.="&keyword={$this->keyword}";
			}
	
	if ($this->cat) {
		$this->condition .= " AND `cat`='{$this->cat}'";
		$this->pars.="&cat={$this->cat}";
		}
	if ($this->limit) {
		$this->pars.="&limit={$this->limit}";
		}
	if ($this->contents) {
		$this->condition.=" AND (`text` LIKE '%{$this->contents}%' OR `title` LIKE '%{$this->contents}%')";
		$this->pars.="&contents={$this->contents}";
		}
	
	
	$this->condition.=" AND p.cat=t.cid ORDER by date DESC";
	//второе условие - для вычисления общего количества постов, подходящих под данные условия
	//можно средствами php массив рубить, но через мускул быстрее...
	$this->condition_w_limit=$this->condition." LIMIT {$this->offset},{$this->limit}";
	
		      
           
   }
   
      //обычный просмотр ленты (учитывая порядок, группировку, лимит и офсет
   function view_all()
   {
      global $cfg,$std,$mysql,$user,$skin;
      $std->title = "Лента";
      $mysql->connect();
              
      $q = $mysql->query("SELECT * FROM posts p,cats t ".$this->condition_w_limit);
      $count=mysql_num_rows($q);
      if ($count==0)
      {
         $this->html.=$std->error("no_entries_found");
         return;
      }
      
      while ($row = mysql_fetch_assoc($q))
      {
         $posts[] = $std->decode_entities($row);
      }
      
            
      //прошустрим все записи:
      foreach ($posts as $post)
      {

      $this->render_entry($post);
      $this->html.=$skin->divisor();
      } 
     
     $this->make_nav_links();
        
      
   }
   
   function make_nav_links()
   {
      global $mysql,$skin;
     
      //выведем линки на следующую и предыдущую страницу, если надо
      
      $mysql->connect();
      $q = $mysql->query("SELECT * FROM posts p,cats t ".$this->condition);
      $count = mysql_num_rows($q);
      if (($count-($this->offset+$this->limit))>0)
      {
      	$back_offset = $this->offset+$this->limit;
	$b_link=$skin->backward("offset={$back_offset}".$this->pars);
	
      }
      
      if (!($this->offset==0))
      {
	$next_offset = $this->offset-$this->limit;
	$f_link=$skin->forward("offset={$next_offset}".$this->pars);
	
      } 

      if ($b_link||$f_link) $this->html.=$skin->navigation($b_link,$f_link);
      
      
    }

   function view_entry()
   {
      global $cfg,$mysql,$user,$skin,$std;
      $mysql->connect();
      if (!$std->input['entry']) 
      {
         $this->html.=$std->error('no_entry_id');
      }
      $q = $mysql->query("SELECT * FROM posts p,cats c WHERE pid = '".$std->input['entry']."' AND p.cat=c.cid");
      if (!$q)
      {
         $this->html.=$std->error("no_such_entry");
         return;
      }
      
      $data = mysql_fetch_assoc($q);
      $this->render_entry($data);
      $this->view_comments(0,$data['pid']);
      $this->comment_form($data);
      
      
   }
   
   //разные типы выборки:
   //0 - дефолтный просмотр; фильтрация неодобренных записей
   //1 - все НЕодобренные комментарии
   //2 - комментарии от определённого пользователя (в $filter кинуть элемент 'uid')
   function view_comments($type=0,$pid=0,$filter=0)
   {
      global $mysql;
      $mysql->connect();
      switch ($type)
      {
      default:
      case 0: $q = $mysql->query("SELECT * FROM comments c,users u WHERE pid='{$pid}' AND c.uid=u.uid ORDER BY `date` asc"); break;
      case 1: $q  = $mysql->query("SELECT * FROM comments c,users u WHERE c.uid=u.uid AND appr=0 ORDER BY `date` asc"); break;
      case 2: $q =  $mysql->query("SELECT * FROM comments c,users u WHERE c.uid='{$filter['uid']}' AND c.uid=u.uid ORDER BY `date` asc"); break;
      }
      
      while ($row = mysql_fetch_assoc($q))
      {
         $this->render_comment($row);
      } 
   }
   
   
   function render_comment($data,$disable_appr_check=0)
   {
      global $user,$std,$cfg,$skin;
      
      $data['date'] = date($cfg['dateformat'],$data['date']);
      
      //фильтрация неодобренных комментариев, если нужно
      if (!$disable_appr_check)
      {
      if (!$data['appr'])
      {
         if ($user->is_admin) $data['approve_link'] = $skin->comment_approve_link($data);
         else 
         {
              $this->html.=$skin->hid_comment();
              return;
	 
         }
	 
      }
      }
      $data['text'] = $std->parse_post($data['text']);
      //$data['text'] = $std->parse_special_tags($data['text']);
      ($data['uid']) ? $data['from'] = $skin->comment_from_link($data) : $data['from'] = $skin->comment_from_link_g($data);
      if ($user->is_admin) $data['delete_link'] = $skin->comment_delete_link($data);
      $this->html.=$skin->comment($data);
      
      
   }
   
   function approve_comment()
   {
      global $std,$mysql,$user;
      
      if (!$user->is_admin) 
      {
      $this->html.=$std->error('no_rights');
      return;
      }
      
      if (!$std->input['cid'])
      {
         $this->html.=$std->error('no_input');
         return;
      } else $cid = intval($std->input['cid']);
      
      $mysql->connect();
      
      $q = $mysql->query("UPDATE comments SET appr = 1 WHERE `cid` = '{$cid}'");
      
      $this->html.=$std->success('comment_approved',0,1);
      
   }

   function render_entry($data)
   {
    global $std,$cfg,$user,$skin,$std;
      if (!$user->is_admin && $data['private'] == true)
      {
         $this->html.=$std->error('no_rights');
         return;
      }
      $data = $std->decode_entities($data);
      $data['date'] = $std->format_date($data['date']);
      if ($data['edit_date']) $data['edit_date'] = $skin->edit_date($std->format_date($data['edit_date'])); else $data['edit_date']='';
      $data['bgcolor'] = $data['color'];
      $data['keywords']=explode(",",$data['keywords']);
      $data['text'] = $std->parse_post($data['text']);
      if ($std->input['opcode']!='viewentry') $data['text'] = $std->parse_special_tags($data); else $data['text'] = $std->parse_special_tags($data,1);
      foreach ($data['keywords'] as $pos=>$keyword)
      {
                  $data['keywords'][$pos] = $skin->keyword($keyword);
      }
         
     
      
      if ($data['keywords']) $data['keywords'] = $skin->keywords(join(', ',$data['keywords']));
      
      if ($user->is_admin) $data['admin_links'] = $skin->admin_links($data);
      if ($data['private']) $data['private'] = '*  '; else $data['private'] = '';
      
      if ($data['caticon']) $data['caticon'] = $skin->caticon($data['caticon'],$data['catname']);
      
      if ($data['music']) $data['music'] = $skin->music($data['music']);
      
      if ($data['moodicon']) $data['moodicon'] = $skin->moodicon($data['moodicon']);  
      
      if ($data['mood']) $data['mood'] = $skin->mood($data['mood']); else $data['mood']='';
      
      $data['cat'] = $skin->category($data['catname'],$data['cid']);

      
      $this->html.=$skin->entry($data);
   }
   
   function delete_entry()
   {
      global $mysql,$std,$user;
      if (!$user->is_admin) 
      {
      $this->html.=$std->error('no_rights');
      return;
      }
      $pid = intval($std->input['entry']);
      $std->title = "Запись удалена";
      $mysql->connect();
      if (!$std->input['entry'])
      {
         $this->html.=$std->error('no_input');
         return;
      }
      $q = $mysql->query("DELETE FROM posts WHERE `pid` = '{$pid}'");
      $this->html.=$std->success('entry_deleted',1);
   }
   
   function edit_form()
   {
      global $mysql,$std,$skin,$user;

      $std->title = "Редактирование записи";
      if (!$user->is_admin) 
      {
      $this->html.=$std->error('no_rights');
      return;
      }
      
      $pid = intval($std->input['entry']);
      
      $mysql->connect();
      $q = $mysql->query("SELECT * FROM posts,cats WHERE pid = '{$pid}' AND posts.cat=cats.cid");
      $data = mysql_fetch_assoc($q);
      //$data = $std->decode_entities($data);
      $data['date'] = $std->format_date($data['date']);
     
      if ($data['private']) $data['private'] = 'checked';
      
      $data['cats_list'] = $std->cats_list($data['cat']);
      $data['keywords_list'] = $std->keywords_list();
      $data['moods_list'] = $std->moods_list();
      $data['opcode'] = 'edit';      
      $data['button1'] = 'Редактировать запись';
      $data['tagbox'] = $skin->tagbox();
      $this->html.=$skin->form($data);
   }
      
      
     
   function add_form()
   {
      global $mysql,$std,$skin,$user;

      $std->title = "Добавление новой записи";
      $mysql->connect();
      if (!$user->is_admin) 
     {
     $this->html.=$std->error('no_rights');
     return;
     }
      $data['cats_list'] = $std->cats_list();
      $data['keywords_list'] = $std->keywords_list();
      $data['moods_list'] = $std->moods_list();
      $data['opcode'] = 'add';
      $data['button1'] = 'Добавить запись';
      $data['tagbox'] = $skin->tagbox();
      $this->html.=$skin->form($data);
   }
   
   function comment_form($data)
   {
      global $skin,$user;
       
      $data['tagbox'] = $skin->tagbox_comment();
      $this->html.=$skin->comment_form($data);
      if (!$user) $this->html.=$skin->guests_comment_notice();
      
   }
   
   function do_add_comment()
   {
      global $std,$mysql,$skin,$cfg,$user,$mail;
      
      if (!$user->uid)
      {
         $approved = 0;
         $uid = 0;
      } else 
      {
      $approved = 1;
      $uid = $user->uid;
      }
      
      echo $uid;
      
      if ($_COOKIE['comment'])
      {
         $this->html.=$std->error('no_flood_comments');
         return;
      } else SetCookie('comment','Weee...',time()+$cfg['flood_timeout']);
      
      if (!$std->input['pid'])
      {
         $this->html.=$std->error('no_input');
         return;
      } else $pid = $std->input['pid'];
      
      if (!$std->input['text'])
      {
         $this->html.=$std->error('text');
         return;
      } else $text = $std->input['text'];
      
      $date = time();
      
      
      $mysql->connect();
      $mysql->query("UPDATE posts SET replies = replies + 1 WHERE pid='{$pid}'");
      $mysql->query("INSERT INTO comments (pid,uid,text,date,appr) VALUES ('{$pid}','{$uid}','{$text}','{$date}','{$approved}')");
      
      if ($approved) 
      {
      	if ($uid!=1) $mail->sendmail('comment_added',1,$cfg['blog_url'].'/entry{$pid}.html');
      } 
      else
      {
      	if ($uid!=1) $mail->sendmail('comment_added_appr',1,$cfg['blog_url']."/entry{$pid}.html");
      }
      
      $this->html.=$std->success('comment_added',1,1);
         
   }
   
   function delete_comment()
   {
      global $std,$mysql,$user;
      
      if (!$user->is_admin)
      {
         $this->html.=$std->error('no_rights');
         return;
      }
      
      if (!$std->input['cid'])
      {
         $this->html.=$std->error('no_input');
         return;
      } else $cid = intval($std->input['cid']);

      $mysql->connect();
      $q = $mysql->query("SELECT * from comments WHERE cid='{$cid}'");
      $q = mysql_fetch_assoc($q);
      $pid = $q['pid'];
      $mysql->query("UPDATE posts SET replies = replies - 1 WHERE pid = '{$pid}'");
      $mysql->query("DELETE FROM comments WHERE cid = '{$cid}' LIMIT 1");
      $this->html.=$std->success('comment_deleted',1,1);
   }
   
   function edit_entry()
   {
      global $std,$mysql,$user;
      
      
      if (!$user->is_admin) 
      {
      $this->html.=$std->error('no_rights');
      return;
      }
      
      $std->title='Редактирование записи...';
      
      if (!$this->check_input()) 
      {
         $this->html.=$std->error('no_input');
         return;
      }
      
      if ($std->input['keywords'])
      {
      foreach (explode(",",$std->input['keywords']) as $keyword)
      {
         if ($keyword!='') $input_keywords[]=trim($keyword);
      }
      $this->add_keywords($input_keywords);     
      $keywords = implode(",",$input_keywords);
      }
      
      $pid = intval($std->input['pid']);
      
      $cat = intval($std->input['cat']);
      
      $text = $std->input['text'];
                             
      $title = $std->input['title'];
      
      if ($std->input['private']) $private = $std->input['private']; else $private = 0;
      
      
      $mood = $std->input['mood'];
      
      $this->add_mood($mood);
     
      $music = $std->input['music'];
      
      $mysql->connect();
       
      $mysql->query("UPDATE posts SET private='{$private}',edit_date='".time()."',text='{$text}',title='{$title}',cat='{$cat}',keywords='{$keywords}',mood='{$mood}',music='{$music}' WHERE pid = '{$pid}';");
      
      $this->html.=$std->success('entry_edited',1);
      
   
   }
   
   function add_entry()
   {
      global $std,$mysql,$user;
      
      
      if (!$user->is_admin) 
      {
      $this->html.=$std->error('no_rights');
      return;
      }
      
      $std->title='Добавление записи...';
      
      
      if (!$this->check_input()) 
      {
         $this->html.=$std->error('no_input');
         return;
      }
      //внесём пост в базу: 
      
      if ($std->input['keywords'])
      {
      foreach (explode(",",$std->input['keywords']) as $keyword)
      {
         if ($keyword!='') $input_keywords[]=trim($keyword);
      }
      $this->add_keywords($input_keywords);     
      $keywords = implode(",",$input_keywords);
      }

      $pid = intval($std->input['pid']);
      
      $text = $std->input['text'];
                       
      $title = $std->input['title'];
      
      $cat = intval($std->input['cat']);
      
      if ($std->input['private']) $private = $std->input['private']; else $private = 0;
      
      
      
      $mood = $std->input['mood'];
      
      $this->add_mood($mood);
     
      $music = $std->input['music'];
      
      $mysql->connect();
      
      $mysql->query("INSERT INTO posts (private,date,text,title,cat,keywords,music,mood) VALUES ('{$private}','".time()."','{$text}','{$title}','{$cat}','{$keywords}','{$music}','{$mood}');");

      $this->html.=$std->success('entry_added',1,1);
   }
   
   function add_keywords($input_keywords)
   {
      global $mysql;
      //внесём новые ключевые слова в базу:
      $keywords = array();
      if (!$input_keywords) return false;      
      $q = $mysql->query("SELECT * FROM keywords");
      if ($q)
      {
         while ($row = mysql_fetch_assoc($q))
         {
            $keywords[] = $row['keyword'];
         }
      }
      foreach ($input_keywords as $input_keyword)
      {
//         $input_keyword = $input_keyword;
         if (!in_array($input_keyword,$keywords)) $keywords_query.="INSERT INTO keywords (keyword) VALUES ('{$input_keyword}'); ";
      }

      if ($keywords_query) $mysql->query($keywords_query,true);
   }
   
 
   function add_mood($mood)
   {
      global $mysql;
      //настроение:
      $moods = array();
      if (!$mood) return false;      
      $q = $mysql->query("SELECT * FROM moods");
      while ($row = mysql_fetch_assoc($q))
         {
            $moods[] = $row['moodname'];
         }
                 
      if (!in_array($mood,$moods)) 
         {
         $mood_name = $mood;
         $mysql->query("INSERT INTO moods (moodname) VALUES ('{$mood_name}')");
         return true;
         }
   }
   
   function check_input()
   {
      global $std;
      
      
      
      foreach (array('text','title','cat') as $input)
      {
         if ($std->input[$input] == '') return FALSE;
      }
      
      
      return TRUE;
      
   }
   
  
   function finish()
   {
      global $skin;
      $this->html=$skin->module($this->html);
   }
}

?>
