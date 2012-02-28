<?php

class skin_entry
{
   
   //форма добавления записи
   function form($data)
   {
      return "
      <table width='95%'>
      <script>
      function validate_form()
      {
         
        if (addentry.cat.value == '' || addentry.title.value == '' || addentry.text.value == '') 
            {
                window.alert('Заполните все поля формы!');
                return false;  
            }
         
         return true;
      }
      </script>
      <form name='addentry' action='index.php' method='post'>
      <tr>
      <td>
      <b>Заголовок:</b><br />
      <input name='title' type='text' size='35' value=\"{$data['title']}\"><br />
      Категория:<br />
      {$data['cats_list']}
      <br />
      </tr>
      <tr>
      <td>
      <b>Текст записи:</b><br />
      <textarea name='text' rows='20' cols='80'>{$data['text']}</textarea>
      </td>
      <td align='center'>
      {$data['tagbox']}
      </td>
      </tr>
      <br>
      <tr>
      <td>
      Музыка:<br />
      <input name='music' type='text' size='35' value=\"{$data['music']}\">
      <br />
      <tr>
      <td>
      Настроение:<br />
      <input name='mood' type='text' size='35' value=\"{$data['mood']}\">&nbsp;{$data['moods_list']}
      <br />
      <tr>
      <td>
      Ключевые слова <i>(через запятую)</i>:<br />
      <input name='keywords' type='text' size='35' value=\"{$data['keywords']}\">&nbsp;{$data['keywords_list']}<br />
      <tr><td>
      <input name='private' type='checkbox' value='1' {$data['private']}>&nbsp;Личная запись? <i>(будет видна только вам)</i>
      <br /><br /><br />
      <tr><td>
      <input type='button' value=\"{$data['button1']}\" onClick=\"if (validate_form()) addentry.submit()\">&nbsp;&nbsp;
      <input type='button' value='Сбросить форму' onClick=\"if (window.confirm('Очистить все поля?')) addentry.reset()\">
      <input name='a' type='hidden' value='entry'><input name='opcode' type='hidden' value=\"{$data['opcode']}\">
      <input name='pid' type='hidden' value=\"{$data['pid']}\">
      </form>
      </tr>
      </table>
      ";
   }
   
   
   /*элемент ленты
   /структура $data:
   title - заголовок записи
   text - содержимое записи
   date - время записи (отформатированное)
   cat - тема
   views - кол-во просмотров
   replies - кол-во ответов
   bgcolor - цвет темы
   keywords - ключевые слова (отформатированные)
   admin_links - ссылки для админа
   
   */
   function entry($data)
   {
      return "
      <table class='heading' bgcolor=\"{$data['bgcolor']}\"><tr>
      <td align='left'>{$data['private']}{$data['caticon']}</a><a href=\"entry{$data['pid']}.html\">{$data['title']}</a></td>
      <td align='right'><span class='time'>{$data['date']}</span></td>
      </tr></table>
      {$data['cat']}
      {$data['text']}<br />
      {$data['moodicon']}{$data['mood']}
      {$data['music']}
      {$data['edit_date']}
      <table width=100% class='bottomline'><tr class='bottomline'>
      <td class='bottomline' align='left'>ID: {$data['pid']} | {$data['keywords']}</td>
      <td class='bottomline' align='right' valign='middle'><a href='entry{$data['pid']}.html'>Комментариев: {$data['replies']}</a>
      {$data['admin_links']} 
      </td>
      </tr></table>
      ";
   }
   
   //форма комментария
   function comment_form($data)
   {
      return "
      <br /><hr>
      <script>
      function validate_comment_form()
      {
         if (addcomment.text.value == '')
         {
            window.alert('Заполните поле текста комментария!');
         } else addcomment.submit();
      }
      </script>
      <table width='95%'>
      <form name='addcomment' action='index.php' method='post'>
      <input type='hidden' value='entry' name='a'>
      <input type='hidden' value='add_comment' name='opcode'>
      <input type='hidden' value='{$data['pid']}' name='pid'>
      <tr>
      Ваш комментарий:<br />
      </tr>
      <tr>
      <td>
      <textarea name='text' rows='10' cols='80'></textarea><br /><br />
      <input type='button' onclick='validate_comment_form();' value='Отправить'>
      <td align='center'>
      {$data['tagbox']}
      </tr>
      </table>
      </form>
      ";
   }
   
   function comment($data)
   {
      return "
      <hr>
      <table class='subheading'>
      <tr ><td align='left'><small>От&nbsp;{$data['from']}</small>
      <td align='right' class='subheading'><span class='time'>{$data['date']}</span>{$data['delete_link']}{$data['approve_link']}
      </tr>
      </table>
      {$data['text']}
      ";
   }
   
   function hid_comment()
   {
      return "
      <hr>
      <div class='hid_comment'>[Ожидающий одобрения комментарий]</div>
      ";
   }
   
   function comment_from_link($data)
   {
      return "<a href=\"user{$data['uid']}.html\">{$data['name']}</a>";
   }
   
    function comment_from_link_g($data)
   {
      return "Гость";
   }
   
   
  function comment_delete_link($data)
  {
     return"
     &nbsp;&nbsp;&nbsp;<a href=\"index.php?a=entry&opcode=delete_comment&cid={$data['cid']}\" title=\"Удалить комментарий\" alt=\"X\"><img src='<#IMG_DIR#>/delete.gif'></a>
     ";
  }
  
   function comment_approve_link($data)
   {
      return "
      &nbsp;<a href='#' onClick='if(window.confirm(\"Одобрить комментарий?\")) location.href=\"index.php?a=entry&opcode=approve_comment&cid={$data['cid']}\";'><img src='<#IMG_DIR#>/approve.gif' title='Одобрить комментарий'></a>
      ";
   }
  
  function comment_avatar($src)
  {
     return "
     <img src='{$src}' alt='Аватар' title='Аватар пользователя'>
     ";
  }
  function guests_comment_notice()
  {
     return "<br>
     <div class='note'>Комментарии гостей становятся общедоступными только после одобрения их владельцем блога</div>
     ";
  }
   function tagbox()
   {
      return "
      <center>
      <b>Теги:</b><br>
      <a title=\"Курсив\" onClick='addentry.text.value+=\"[i][\/i]\";'>[i][/i]</a>
      &nbsp;&nbsp;
      <a title=\"Жирный\" onClick='addentry.text.value+=\"[b][\/b]\";'>[b][/b]</a>
      &nbsp;&nbsp;
      <a title=\"Подчёркнутый\" onClick='addentry.text.value+=\"[u][\/u]\";'>[u][/u]</a>
      &nbsp;&nbsp;
      <a onClick='addentry.text.value+=\"[em][\/em]\";'>[em][/em]</a>
      <br />
      <a onClick='addentry.text.value+=\"[big][\/big]\";'>[big][/big]</a>
      &nbsp;&nbsp;
      <a onClick='addentry.text.value+=\"[small][\/small]\";'>[small][/small]</a>
      <br />
      <a onClick='addentry.text.value+=\"[*]\";' title='Пункт списка'>[*]</a>
      &nbsp;&nbsp;
      <a onClick='addentry.text.value+=\"[cut]\";'>[cut]</a>
      &nbsp;&nbsp;
      <a title=\"Текст будет виден только друзьям\" onClick='addentry.text.value+=\"[vip][\/vip]\";'>[vip]</a>
      <br />
      <a title=\"[IMG=адрес_картинки]\" onClick='addentry.text.value+=\"[IMG=]\";'>Изображение</a>
      &nbsp;&nbsp;
      <a title=\"[URL=ссылка]Видимый текст[/URL]\" onClick='addentry.text.value+=\"[URL=][\/URL]\";'>Ссылка</a>
      &nbsp;&nbsp;
      </center>
       <a title=\"[FILE=id_файла]Видимый текст[/FILE]onClick='addentry.text.value+=\"[FILE=][\/FILE]\";'>Загруженный файл</a>
      &nbsp;&nbsp;
      </center>
      ";
   }
   
    function tagbox_comment()
   {
      return "<center>
      <b>Теги:</b><br>
      <a title=\"Курсив\" onClick='addcomment.text.value+=\"[i][\/i]\";'>[i][/i]</a>
      &nbsp;&nbsp;
      <a title=\"Жирный\" onClick='addcomment.text.value+=\"[b][\/b]\";'>[b][/b]</a>
      &nbsp;&nbsp;
      <a title=\"Подчёркнутый\" onClick='addcomment.text.value+=\"[u][\/u]\";'>[u][/u]</a>
      &nbsp;&nbsp;
      <a onClick='addcomment.text.value+=\"[em][\/em]\";'>[em][/em]</a>
      <br />
      <a onClick='addcomment.text.value+=\"[big][\/big]\";'>[big][/big]</a>
      &nbsp;&nbsp;
      <a onClick='addcomment.text.value+=\"[small][\/small]\";'>[small][/small]</a>
      <br />
      <a onClick='addcomment.text.value+=\"[*]\";' title='Пункт списка'>[*]</a>
      &nbsp;&nbsp;
      <a title=\"Текст будет виден только друзьям\" onClick='addcomment.text.value+=\"[vip][\/vip]\";'>[vip]</a>
      <br />
      <a onClick='addcomment.text.value+=\"[IMG=]\";'>Изображение</a>
      &nbsp;&nbsp;
      <a onClick='addcomment.text.value+=\"[URL=][\/URL]\";'>Ссылка</a>
      &nbsp;&nbsp;
      </center>
      ";
   }
   
   function music($str)
   {
      return "
      <span class='music'>Слушаю:&nbsp;<a href='http://www.altavista.com/audio/results?q={$str}' title='Поиск музыки'>{$str}</a></span><br />
      ";
   }
   
   function mood($mood)
   {
      return "
      <span class='mood'>Настроение:&nbsp;<a href='index.php?mood={$mood}'>{$mood}</a></span><br />
      ";
   }
   
   function moodicon($img)
   {
      return "<img src='<#IMG_DIR#>/{$img}'>&nbsp;";
   }
   
   function caticon($img,$catname)
   {
      return "<img src='<#IMG_DIR#>/{$img}' alt='{$catname}' title='{$catname}'>&nbsp;";
   }
   
   function category($cat,$id)
   {
      return "
      <span class='category'>Категория:&nbsp;<a href=\"index.php?cat={$id}\" class='category'>{$cat}</a></span><br />
      ";
   }
   
   function admin_links($data)
   {
      return "
      &nbsp;<a href='#' onClick='if(window.confirm(\"Редактировать запись?\")) location.href=\"index.php?a=entry&opcode=edit_form&entry={$data['pid']}\";'><img src='<#IMG_DIR#>/edit.gif' title='Редактировать' border=0></a>
      &nbsp;<a href='#' onClick='if(window.confirm(\"Удалить запись?\")) location.href=\"index.php?a=entry&opcode=delete&entry={$data['pid']}\";'><img src='<#IMG_DIR#>/delete.gif' title='Удалить' border=0></a>
      {$data['approve_link']}
      ";
   }
   
   
   function keywords($str)
   {
      return "<span class='keywords'>Ключевые слова: {$str}</span>";
   }
     
   function keyword($str)
   {
      return "<a href='index.php?keyword={$str}'>{$str}</a>";
   }
   
   
   function navigation($b_link,$f_link)
   {
      return "<div class='navigation'>{$b_link} | {$f_link}</div>";
   }
   
   function backward($pars="")
   {
      return "<a href='index.php?{$pars}'><< Прошлые записи</a>";
   }
   
   function forward($pars="")
   {
      return "<a href='index.php?{$pars}'> Следующие записи >></a>";
   }
   
   function edit_date($date)
   {
      return "
      <span class='edit_date'>Редактировалось {$date}</span>
      <br />
      ";
   }

   
   function divisor()
   {
      return "<hr>";
   }
   
   function module($data)
   {
      return "
      <div class='module'>{$data}</div>
      ";
   }
}

?>
