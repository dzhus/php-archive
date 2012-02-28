<?php

class skin_reg
{
   function reg_form($cfg)
   {
      return "
      <script>
      function validate_form()
      {
         if (window.confirm('Зарегистрировать пользователя?')) return true;
      }
      </script>
      
      <center><h2>Регистрация</h2>
      <div class='desc'>Регистрация даёт право отправлять комментарии без ожидания одобрения их владельцем журнала, и просматривать записи, помеченные как \"только для друзей\".</div></center>
      <br /><br />
      <form name='register' enctype=\"multipart/form-data\" action='index.php' method='post'>
      <input name=\"opcode\" type=\"hidden\" value=\"register\">
      <input name=\"a\" type=\"hidden\" value=\"reg\">
      <input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"{$cfg['max_photo_size']}\" >
      <table align='center' width='70%'>
      <tr>
      <td class='cell1' width='20%'>Имя
      <td class='cell3'><input size='75' name=\"name\" type=\"text\" value=\"\">
      <tr>
      <td class='cell1'>Пароль
      <td class='cell3'><input type=\"password\" size='75' name=\"pass\"><br />
      <tr><td height='15px'><tr>
      <td class='cell1' width='20%'>Фото/аватар
      <td class='cell3'><input size='64' onChange = \"if (this.value!='') { document.register.photo_url.disabled=true; document.register.photo_url.value=''; } else document.register.photo_url.disabled=false;\" name=\"photo_file\" type=\"file\" /><br />
      <i><small>Максимальный размер - {$cfg['max_photo_size']} байт.</small></i>
      <tr>
      <td class='cell1' width='20%'>URL фото/аватара 
      <td class='cell3'><input size='75' name=\"photo_url\" type=\"text\" value=\"{$data['photo_url']}\"/><br />
      <i><small>Укажите URL или загрузите своё изображение. Допустимые расширения: GIF, JPEG, JPG, PNG. Допустимые размеры: {$cfg['max_photo_w']}x{$cfg['max_photo_h']}</small></i>
      <tr>
      <td class='cell1'>E-mail
      <td class='cell3'><input size='75' name=\"mail\" type=\"text\" value=\"\"><br />
      <tr>
      <td class='cell1'>ICQ
      <td class='cell3'><input size='75' name=\"icq\" type=\"text\" value=\"\">
      <tr>
      <td class='cell1'>WWW                                                               
      <td class='cell3'><input size='75' name=\"www\" type=\"text\" value=\"\">
      <tr>
      <td class='cell1'>Дата рождения
      <td class='cell3'><input size='75' name=\"birthday\" type=\"text\" value=\"\"><br />
      <i><small>В формате ДД.ММ.ГГГГ, например: 01.01.1970</small></i>
      <tr><td height='15px'></tr>
      </table>
      <center>
      <input type='button' onClick=\"if (validate_form()) register.submit()\" value='Зарегистрироваться'>&nbsp;&nbsp;&nbsp;
      <input type='button' value='Очистить форму' onClick=\"if (window.confirm('Очистить все поля?')) register.reset()\">
      </center>
      </form>
      ";
   }
   
   function module($data)
   {
      return "
      <div class='module'>{$data}</div>
      ";
   }
}

?>
