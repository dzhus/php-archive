<?php

class skin_profile
{
   function mail($text,$link)
   {
      return "
      <a href='{$link}' title='Написать письмо владельцу'>{$text}</a>
      ";
   }   
   
   function photo($src)
   {
      return "<img src='{$src}' class='photo'>";
   }
   
   function geekcode_link($txt,$enc)
   {
      return "
      <a href='http://www.geekclub.ru/decode.php?gcode={$enc}'>{$txt}</a>
      ";
   }
   
   function www_link($site)
   {
      return "
      <a href='{$site}' title='Страница пользователя'>{$site}</a>
      ";
   }
   function icq_status($num)
   {
       return "
       <img src=\"http://status.icq.com/online.gif?icq={$num}&img=5\">
       ";
   }
   
   function profile_view($data)
   {
      return "
      <table align='center' width='70%'>
      
      <td valign='middle'>{$data['photo']}</td>
      <td valign='middle'>
      <table width='100%'>
      <tr>
      <td class='cell1' width='20%'>Имя
      <td class='cell2'><a href=\"index.php?a=profile&opcode=view&uid={$data['uid']}\">{$data['name']}</a>
      <tr>
      <td class='cell1'>Зарегистрировался
      <td class='cell2'>{$data['joined']}
      <tr>
      <td class='cell1'>Дата рождения
      <td class='cell2'>{$data['birthday']}
      <tr>
      <td class='cell1'>E-mail
      <td class='cell2'>{$data['mail']}
      <tr>
      <td class='cell1'>ICQ
      <td class='cell2'>{$data['icq_status']}&nbsp;{$data['icq']}
      <tr>
      <td class='cell1'>WWW
      <td class='cell2'>{$data['www']}
      <tr>
      <td class='cell1'>Гик-код
      <td class='cell2'>{$data['geekcode']}
      </table>
      </td>      
      </table>
      ";
   }
   
   function edit_profile($data,$cfg)
   {
      return "
      <script>
      function validate_form()
      {
         if (!document.profile.old_pass.value)
         {
            window.alert('Для изменения профиля необходимо ввести текущий пароль');
            return false;
         }
         if (window.confirm('Редактировать профиль?')) return true;
      }
      </script>
      
      <form name='profile' enctype=\"multipart/form-data\" action='index.php' method='post'>
      <input name=\"opcode\" type=\"hidden\" value=\"do_edit\">
      <input name=\"a\" type=\"hidden\" value=\"profile\">
      <input name=\"uid\" type=\"hidden\" value=\"{$data['uid']}\">
      <table align='center' width='70%'>
      <td valign='middle'>{$data['photo']}</td>
      <td valign='middle'>
      <table width='100%'>
      <tr>
      <td class='cell1' width='20%'>Имя
      <td class='cell3'><a href=\"index.php?a=profile&opcode=view&uid={$data['uid']}\">{$data['name']}</a>
      <tr>
      <td class='cell1' width='20%'>Фото/аватар
      <td class='cell3'><input size='64' onChange = \"if (this.value!='') { document.profile.photo_url.disabled=true; document.profile.photo_url.value=''; } else document.profile.photo_url.disabled=false;\" name=\"photo_file\" type=\"file\" /><br />
      <i><small>Максимальный объём - {$cfg['max_photo_size']} кбайт.</small></i>
      <tr>
      <td class='cell1' width='20%'>URL фото
      <td class='cell3'><input size='75' name=\"photo_url\" type=\"text\" value=\"{$data['photo_url']}\" onChange = \"if (this.value!='') { document.profile.photo_file.disabled=true; document.profile.photo_file.file=''; } else document.profile.photo_file.disabled=false;\"/><br />
      <i><small>Укажите URL или загрузите файл с изображением. <i>Не забывайте http://</i> Допустимые разрешения: GIF, JPEG, JPG, PNG. Допустимые размеры: {$cfg['max_photo_w']}x{$cfg['max_photo_h']}</small></i>
      <tr>
      <td class='cell1'>E-mail
      <td class='cell3'><input size='75' name=\"mail\" type=\"text\" value=\"{$data['mail']}\"><br />
      <i><small>Укажите свой <b>реальный</b> адрес электронной почты, иначе вы не сможете получать сервисные сообщения, например, для восстановления забытого пароля</small></i>
      <tr>
      <td class='cell1'>ICQ
      <td class='cell3'><input size='75' name=\"icq\" type=\"text\" value=\"{$data['icq']}\">
      <tr>
      <td class='cell1'>WWW
      <td class='cell3'><input size='75' name=\"www\" type=\"text\" value=\"{$data['www']}\">
      <tr>
      <td class='cell1'>Дата рождения
      <td class='cell3'><input size='75' name=\"birthday\" type=\"text\" value=\"{$data['birthday']}\"><br />
      <i><small>В формате ДД.ММ.ГГГГ, например: 01.01.1970</small></i>
      <tr>
      <td class='cell1'>Гик-код
      <td class='cell3'><textarea cols='56' rows='2' name=\"geekcode\">{$data['geekcode']}</textarea><br />
      <i><small><a href=\"http://faq.geekclub.ru\">Что это такое?</a></small></i>
      <tr><td height='15px'></tr>
      <tr>
      <td class='cell1'>Текущий пароль
      <td class='cell3'><input type=\"password\" size='75' name=\"old_pass\" value=\"{$data['old_pass']}\">
      <tr>
      <td class='cell1'>Новый пароль
      <td class='cell3'><input type=\"password\" size='75' name=\"new_pass\"><br />
      <i><small>Введите новый пароль, если хотите его сменить</small></i>
      </table>
      </td>
      </table>
      <center>
      <input type='button' onClick=\"if (validate_form()) profile.submit()\" value='Редактировать'>
      </center>
      </form>
      ";
   }
   
   function edit_profile_link($uid)
   {
      return 
      "<br /><center><a href='index.php?a=profile&opcode=edit&uid={$uid}' title='Редактирование профиля'>Редактировать профиль</a></center>";
   }
   
      function module($data)
   {
      return "
      <div class='module'>{$data}</div>
      ";
   }
}

?>