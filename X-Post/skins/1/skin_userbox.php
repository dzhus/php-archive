<?php

class skin_userbox
{
  
function authorized($name)
{
return "
Привет, {$name}.&nbsp;
<a href='#' onClick='if(window.confirm(\"Произвести выход из системы?\")) location.href=\"index.php?a=auth\";'><img src='skins/1/key_out.gif' title='Выход из системы' border=0></a>
<li><a href=\"index.php\" title=\"Переход на главную страницу\">Домой</a>
";
}

function add_entry_link()
{
return "
<li><a href=\"index.php?a=entry&opcode=add_form\" title=\"Кликните, чтобы добавить запись в блог\">Добавить запись</a>
";
}

function files_link()
{
return "
<li><a href='index.php?a=file'>Файлы</a>
";
}

function files_link_admin()
{
return "
<li><a href='index.php?a=admin&opcode=files'>Файлы</a> (<a href='index.php?a=admin&opcode=add_file'>+</a>)
";
}

function profile_link($uid)
{
return "
<li><a href='user{$uid}.html' title='Кликните, чтобы просмотреть/отредактировать свой профиль'>Мой профиль</a>
";
}

function acp_link()
{
return "
<li><a href='index.php?a=admin' title='Кликните, чтобы перейти в Панель управления системой'>Панель управления</a>
";
}

function reg_link()
{
return "
<li><a href='index.php?a=reg' title='Кликните, чтобы зарегистроваться в системе'>Хочу во френды</a>
";
}

function sbox_link()
{
return "
<li><a href='index.php?a=sbox' title='SpeakerBoxx'>SpeakerBoxx</a>
";
}

function not_authorized()
{
return "
Привет, гость.&nbsp;
<span style='text-align:right'><a href='index.php?a=auth'><img src='skins/1/key.gif' title='Вход в систему' border=0></a></span>
<li><a href=\"index.php\" title=\"Переход на главную страницу\">Домой</a>
";
}

function module($data)
{
return "
<div class='module'>
<div class='subheading'>
Пользователь</div>
{$data}
</div>
";
}
}

?>