<?php

class skin_rss
{



 function entry($data)
 {

      return "
      <table class='rss_heading'><tr>
      <td align='left'><a href=\"{$data['link']}\">{$data['author']}</a>&nbsp;пишет:&nbsp;<a href=\"{$data['link']}\">{$data['title']}</a></td>
      <td align='right'><span class='time'>{$data['date']}</span></td>
      </tr></table>
      {$data['post']}<br />
      <table width=100% class='bottomline'><tr class='bottomline'>
       <td class='bottomline' align='left' valign='middle'><a href=\"{$data['link']}\">Просмотр записи</a></td>
      <td class='bottomline' align='right'><a href=\"{$data['comment_link']}\">Комментарии</a></td>
     
      {$data['admin_links']} 
      </td>
      </tr></table>
      <hr />
      ";
   
 }
 
 function module($data)
 {
    return "
    <div class='module'>
    <h1>Френдлента</h1><br />
    {$data}
    </div>
    ";
 }
   
}

$skin = new skin_rss;

?>