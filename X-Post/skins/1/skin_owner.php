<?php                                                

class skin_ownerbox
{

   
      
   function epoch_counter($name,$epoch)
   {
      return "
      <form name='epoch'>
      <span title='Время со дня рождения {$name} в секундах'>{$name} Epoch</span> <input name='counter' type='text' value='{$epoch}' size='10'> s
      </form>
      <script>
      setInterval('document.epoch.counter.value++',1000);
      </script>";
   }
   
   function owner_box($data)
   {
      return "
      <div class='subheading'>
      Автор</div><br />
      <a href='user{$data['uid']}.html'>{$data['name']}</a> (<small>{$data['mail']}</small>)
      {$data['epoch']}<br>
      <center>{$data['photo']}<br>
      <a href='http://sphinxcollectibles.net.ru'><small>&copy; Sphinx Collectibles</small></a></center>
      ";
   }
   
   function mail($text,$link)
   {
      return "
      <a href='{$link}' title='Написать письмо владельцу'>{$text}</a>
      ";
   }
   function photo($src,$w='',$h='')
   {
      return "
      <img id='photo' {$w} {$h} src='{$src}' title='Фото владельца блога' alt='Фото'>
      ";
   }
   function module($data)
   {
      return "
      <div class='module'>
      {$data}
      </div>
      ";
   }
   
}


?>