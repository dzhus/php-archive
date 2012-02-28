<?php

//std class
//хранение сервисных переменных
//сервисные функции и рутины
class std
{

var $title;
   
   function check_var($variable,$poss)
   {
      if ((gettype($poss)=='array') and (!in_array($variable,$poss))) die("Попытка взлома. Аварийное завершение.");
      if (($poss=='int') and (gettype($variable)!='integer')) die("Попытка взлома. Аварийное завершение.");
   }
   
   function skin_write($skin='1')
   {
      if ($this->input['skin']) $skin = $this->input['skin'];
      if (!$this->skin_read()) setCookie('selected_skin',$skin,time()*2); 
   }
   
   function skin_read()
   {
            if (!$_COOKIE['selected_skin']) $skin = '1'; else $skin = $_COOKIE['selected_skin'];
            return $skin;
   }
   
   function error($msg,$mainlink=true,$backlink=true)
   {
      global $global_skin;
      require('errors.php');
      $html.=$global_skin->error($errors[$msg]);
      if ($mainlink) $html.=$global_skin->main_link();
      if ($backlink) $html.=$global_skin->back_link($_SERVER['HTTP_REFERER']);
      return $html;
   }
   
   function success($msg,$mainlink=false,$backlink=false)
   {
      global $global_skin;
      require('status.php');
      $html=$global_skin->status($status[$msg]);
      if ($mainlink) $html.=$global_skin->main_link();
      if ($backlink) $html.=$global_skin->back_link($_SERVER['HTTP_REFERER']);
      return $html;
   }
   
  
   
   function parse_post($txt)
   {
      foreach( array ('b'=>'b',
                        'i'=>'i',
                        'u'=>'u',
                        'small'=>'small',
                        'big'=>'big',
                        'em'=>'em'
                                               
                        ) as $code=>$tag)
                        {
                           $txt=preg_replace("#\[".$code."\](.+?)\[\/".$code."\]#si","<{$tag}>$1</{$tag}>",$txt);
                        }
      $txt = str_replace('[*]','<li>',$txt);
      $txt = preg_replace("#\[URL=(.+?)\](.+?)\[\/URL\]#si","<a href='$1'>$2</a>",$txt);
      $txt = preg_replace("#\[IMG=(.*)\]#si","<img src='$1'>",$txt);
      $txt = preg_replace("#\[FILE=(\d+)\](.+?)\[\/FILE\]#si","<a href='/file$1'>$2</a>",$txt);
      $txt = nl2br($txt);
      return $txt;
   }
   
   function parse_special_tags($data,$fullview=false)
   {
      global $user,$global_skin;
      if ($fullview) $txt = str_replace("[cut]","",$data['text']);
      else
      $txt = preg_replace("#\[cut\].*#si","<br /><a href=\"/entry{$data['pid']}.html\" title=\"Просмотр полной записи\">Просмотр продолжения &gt;&gt;</a>",$data['text']);
      if ($user->uid)
      {
         $txt = preg_replace("#\[vip\](.+?)\[\/vip\]#si",$global_skin->vip_txt("$1"),$txt);
      } else $txt = preg_replace("#\[vip\](.+?)\[\/vip\]#si",$global_skin->vip_txt_hidden(),$txt);
      
      return $txt;
   }
   
   function strip_codes($txt)
   {
	foreach( array ('b'=>'b',
                        'i'=>'i',
                        'u'=>'u',
                        'small'=>'small',
                        'big'=>'big',
                        'em'=>'em'
                                               
                        ) as $code=>$tag)
                        {
                           $txt=preg_replace("#\[".$code."\](.+?)\[\/".$code."\]#si","$1",$txt);
                        }
	$txt = preg_replace("#\[URL=(.+?)\](.+?)\[\/URL\]#si","$1",$txt);
	$txt = preg_replace("#\[IMG=(.*)\]#si","$1",$txt);
	$txt = str_replace("[cut]","",$txt);
	$txt = str_replace(array('[vip]','[/vip]'),array(),$txt);
	return $txt;
   }
   
   function format_date($timestamp)
   {
      global $cfg;
      return date($cfg['dateformat'],$timestamp);
   }
   
   function transliterate($string)
   {
   
   $letters_ru = array("А","Б","В","Г","Д","Е","Ё","Ж","З","И","Й","К","Л","М","Н","О","П","Р","С","Т","У","Ф","Х","Ц","Ч","Ш","Щ","Ы","Э","Ю","Я","а","б","в","г","д","е","ё","ж","з","и","й","к","л","м","н","о","п","р","с","т","у","ф","х","ц","ч","ш","щ","ы","э","ю","я"," ");
   $letters_en = array("A","B","V","G","D","E","E","ZH","Z","I","J","K","L","M","N","O","P","R","S","T","U","F","H","TS","CH","SH","SH","Y","E","JU","JA", "a","b","v","g","d","e","e","zh","z","i","j","k","l","m","n","o","p","r","s","t","u","f","h","ts","ch","sh","sh","y","e","ju","ja","_");
   
   return str_replace($letters_ru,$letters_en,$string);
   
   } 
  
   function parse_email($email)
   {
      $email=str_replace("@","<img src='doggy.gif'>",$email);
      return $email;
   }
   
   function parse_email_link($email)
   {
      $email="mailto:".str_replace(array("@","."),array("[at]","[dot]"),$email);
      return $email;
   }
   
   function print_all($wrapper)
   {
   global $html,$std,$cfg;   
   $wrapper = file_get_contents($wrapper);
      foreach ($html as $part=>$contents)
      {
	     
         $wrapper = str_replace('{'.$part.'}',$contents,$wrapper);
      }
   $wrapper = str_replace('<#IMG_DIR#>',$cfg['skin_dir'].'/'.$std->skin_read(),$wrapper);
   //самый главный вывод во всём скрипте:
   print $wrapper;
   }
   
   function do_inputs()
   {
      foreach ($_GET as $key=>$value)
      {
	      $this->input[$key] = htmlspecialchars($value,ENT_QUOTES);
      }
      foreach ($_POST as $key=>$value)
      {
	      $this->input[$key] = htmlspecialchars($value,ENT_QUOTES);
	 
      }
   }
   
   function decode_entities($in)
   {
      if (gettype($in)=='array')
      {
         
         foreach ($in as $key=>$value)
                  {
                           $in[$key] = html_entity_decode($value);
                  }
      }
      else $in = html_entity_decode($in);
      return $in;
   }
   
   
   function cats_list($default='')
   {
      global $mysql,$global_skin;
      //формируем список тем:
      $q = $mysql->query("SELECT * FROM cats ORDER BY catname ASC");
      if ($q)
      {
         while ($row = mysql_fetch_assoc($q))
         {
            $row['cid'] == $default ? $row['sel'] = 'selected' : $row['sel'] = '';
	    $data['cats'][] = $global_skin->cats_row($row);
         }
      }
      if ($data['cats']) $data['cats'] = $global_skin->cats_list(join("",$data['cats']));
      else $data['cats'] = $global_skin->cats_list();
      return $data['cats'];
   }
   
   function moods_list()
   {
      global $mysql,$global_skin;
      //формируем список настроений:
      $q = $mysql->query("SELECT * FROM moods ORDER BY moodname ASC");
      if ($q)
      {
          while ($row = mysql_fetch_assoc($q))
          {
                 $data['moods'][] = $global_skin->moods_row($row);
          }
      }
      if ($data['moods']) $data['moods'] = $global_skin->moods_list(join("",$data['moods']));
      else $data['moods'] = $global_skin->moods_list();
      return $data['moods'];
   }
   
   function keywords_list()
   {
      global $mysql,$global_skin;
      //и список ключевых слов:
      $q = $mysql->query("SELECT * FROM keywords ORDER BY keyword ASC");
      if ($q)
      {
         while ($row = mysql_fetch_assoc($q))
         {
            $data['keywords'][] = $global_skin->keywords_row($row);
         }
      }
      if ($data['keywords']) $data['keywords'] = $global_skin->keywords_list(join("",$data['keywords']));
      else $data['keywords'] = $global_skin->keywords_list();
      return $data['keywords'];
   }
   
      
   function std()
   {
      $this->do_inputs();
   }

   
}

$std = new std();

?>
