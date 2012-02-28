<?php

class skin_admin
{
	function module($data)
	{
	return "
	<div class='module'>
	{$data}
	</div>
	";
	}
	
	function wrapper($data)
	{
	return "
	<table width='95%' align='center'>
	<tr align='center'>
	<td width='20%' class='cell1' onMouseOver='this.className=\"cell1hl\"' onMouseOut='this.className=\"cell1\"'><a href='index.php?a=admin&opcode=cats'>Категории</a>
	<td width='20%' class='cell1' onMouseOver='this.className=\"cell1hl\"' onMouseOut='this.className=\"cell1\"'><a href='index.php?a=admin&opcode=moods'>Настроения</a>
	<td width='20%' class='cell1' onMouseOver='this.className=\"cell1hl\"' onMouseOut='this.className=\"cell1\"'><a href='index.php?a=admin&opcode=keywords'>Ключевые слова</a>
	<td width='20%' class='cell1' onMouseOver='this.className=\"cell1hl\"' onMouseOut='this.className=\"cell1\"'><a href='index.php?a=admin&opcode=users'>Пользователи</a>
	<td width='20%' class='cell1' onMouseOver='this.className=\"cell1hl\"' onMouseOut='this.className=\"cell1\"'><a href='index.php?a=admin&opcode=files'>Файлы</a>
	</tr>
	<tr>
	<td width='50%' colspan='3' class='cell1' onMouseOver='this.className=\"cell1hl\"' onMouseOut='this.className=\"cell1\"'><a href='index.php?a=admin&opcode=cblox'>Блоки</a>
	<td width='50%' colspan='3' class='cell1' onMouseOver='this.className=\"cell1hl\"' onMouseOut='this.className=\"cell1\"'><a href='index.php?a=admin&opcode=wrappers'>Шаблон</a> 
	</tr>
	</table> <br /><br />
	<div align='center' width='75%' >
	{$data}
	</div><br /><br />
	";
	}
	
	function idx_page()
	{
	return "
	Добро пожаловать в Панель управления системой X-Post! Здесь вы можете изменить основные настройки системы, списки категорий, ключевых слов, настроений. Также отсюда вы можете управлять пользователями, загруженными файлами и создавать пользовательские блоки.
	";
	}
	
	function cats_row($row)
	{
	return "
	<tr bgcolor=\"{$row['color']}\"><td>{$row['cid']}<td width=\"30%\">{$row['catname']}<td>{$row['color']}<td>Иконка:&nbsp;{$row['caticon']}<td><center><a href=\"index.php?a=admin&opcode=edit_cat_form&cid={$row['cid']}\" alt='*' title='Правка и удаление'><img src=\"<#IMG_DIR#>/edit.gif\"></a></center></tr>
	";
	}
	
	function cats_list($rows)
	{
	return "
	<h2><center>Категории</h2>
	<br />
	<div class='desc'>Здесь вы можете редактировать названия, цвета и иконки категорий или удалять их, а также добавлять новые категории.</div><br />
	<a href=\"index.php?a=admin&opcode=add_cat_form\">[Добавить категорию]</a></center><br><br>
	<table align='center' width='80%'>
	<tr><th>ID<th>Название<th>Цвет<th>Иконка<th>Правка/удаление</tr>
	{$rows}
	</table>
	<br /><br />
	<a href=\"index.php?a=admin&opcode=add_cat_form\">[Добавить категорию]</a></center>
	";
	}
	
	function moods_row($mood)
	{
	return "
	<tr><td class='cell3'>{$mood}<td class='cell3'><center><a onclick=\"if (window.confirm('Удалить настроение из списка?')) window.location.href='index.php?a=admin&opcode=delete_mood&mood={$mood}'\" alt='*' title='Удалить настроение из списка' href='#'><img src=\"<#IMG_DIR#>/delete.gif\"></a></center></tr>
	";
	}
	
	function moods_list($rows)
	{
	return "
	<h2><center>Настроения</h2>
	<br />
	<div class='desc'>Вы можете удалить неиспользуемое настроение из списка. Новые настроения добавляются автоматически при отправке новой записи.</div><br />
	<table align='center' width='50%'>
	<tr><th>Настроение<th>Удаление</tr>
	{$rows}
	</table>
	";
	}
	
	function keywords_row($row)
	{
	return "
	<tr><td class='cell3'>{$row['keyword']}<td class='cell2'><center><a onclick=\"if (window.confirm('Удалить ключевое слово из списка?')) window.location.href='index.php?a=admin&opcode=delete_keyword&keyword={$row['keyword']}'\" alt='*' title='Удалить ключевое слово из списка' href='#'><img src=\"<#IMG_DIR#>/delete.gif\"></a></center></tr>
	";
	}
	
	function keywords_list($rows)
	{
	return "
	<h2><center>Ключевые слова</h2>
	<br />
	<div class='desc'>Вы можете удалить неиспользуемые ключевые слова из списка. Новые ключевые слова добавляются автоматически при отправке новой записи.</div><br />
	<table align='center' width='50%'>
	<tr><th>Ключевое слово<th>Удаление</tr>
	{$rows}
	</table>
	";
	}
	
	function icon($ico)
	{
	return "
	<img src='<#IMG_DIR#>/{$ico}' title='Иконка'>
	";
	}
	
	function edit_cat_form($data)
	{
	return "
	<form name=\"edit_cat\" action=\"index.php\" method=\"post\">
	<input name=\"a\" type=\"hidden\" value=\"admin\">
	<input name=\"opcode\" type=\"hidden\" value=\"do_edit_cat\">
	<input name=\"cid\" type=\"hidden\" value=\"{$data['cid']}\">
	Название категории:<br />
	<input name=\"catname\" type=\"text\" value=\"{$data['catname']}\" size=\"45\"><br /><br />
	Цвет категории:<br />
	<input name=\"color\" type=\"text\" value=\"{$data['color']}\" size=\"45\"><br />
	<i><small>Введите hex-код  или название стандартного цвета, например: <b>#FDA700</b>, <b>silver</b>. Можете не заполнять это поле, тогда цвет будет сгенерирован случайным образом</small></i><br /><br />
	Иконка категории:<br />
	<input name=\"icon\" type=\"text\" value=\"{$data['caticon']}\" size=\"45\"><br />
	<i><small>Имя файла в директории скина, например: <b>icon.gif</b></small></i>
	<br /><br />
	<input onClick=\"if (this.checked) cat_sel.disabled=false; else cat_sel.disabled=true;\" name=\"delete\" type=\"checkbox\" value=\"1\">&nbsp;Удалить категорию?
	<br />
	{$data['cats_list']}<br />
	<i><small>В какую категорию переместить посты удаляемой категории?</small></i>
	<br /><br />
	<input type=\"button\" value=\"Редактировать\" onclick=\"if (edit_cat.catname.value=='') window.alert('Введите название категории!'); else edit_cat.submit();\">
	</form>
	
	";
	}
	
	function add_cat_form()
	{
	return "
	<form name=\"add_cat\" action=\"index.php\" method=\"post\">
	<input name=\"a\" type=\"hidden\" value=\"admin\">
	<input name=\"opcode\" type=\"hidden\" value=\"do_add_cat\">
	Название категории:<br />
	<input name=\"catname\" type=\"text\" value=\"{$data['catname']}\" size=\"45\"><br /><br />
	Цвет категории:<br />
	<input name=\"color\" type=\"text\" value=\"{$data['color']}\" size=\"45\"><br />
	<i><small>Введите hex-код  или название стандартного цвета, например: <b>#FDA700</b>, <b>silver</b>. Можете не заполнять это поле, тогда цвет будет сгенерирован случайным образом</small></i><br /><br />
	Иконка категории:<br />
	<input name=\"icon\" type=\"text\" value=\"{$data['caticon']}\" size=\"45\"><br />
	<i><small>Имя файла в директории скина, например: <b>icon.gif</b></small></i>
	<br /><br />
	<input type=\"button\" value=\"Добавить\" onclick=\"if (add_cat.catname.value=='') window.alert('Введите название категории!'); else add_cat.submit();\">
	</form>
	
	";
	
	}
	
	function cats_list_selector($rows='')
	{
	return "
	<select name='cat_sel' disabled>
	{$rows}
	</select>
	";
	}
	
	function cats_row_selector($row)
	{
	return "
	<option value=\"{$row['cid']}\" style=\"background-color:{$row['color']}\">{$row['catname']}</option>
	";
	}
	
	function users_row($row,$uid)
	{
	return "
	<tr>
	<td class='cell3'>{$row['uid']}
	<td class='cell3'><a href=\"user{$row['uid']}.html\" target='_blank'>{$row['name']}</a>
	<td class='cell3'><center><a href=\"index.php?a=profile&opcode=edit&uid={$row['uid']}\" title='Редактировать пользователя' alt='Правка'><img src=\"<#IMG_DIR#>/edit.gif\"></a></center>
	<td class='cell3'><center><a onclick='if (window.confirm(\"Удалить пользователя?\")) window.location.href=\"index.php?a=admin&opcode=delete_user&uid={$uid}\"' href='#' title='Удалить пользователя' alt='Удаление'><img src=\"<#IMG_DIR#>/delete.gif\"></a></center>
	<td class='cell3'><center>{$row['a_link']}</center></tr>
	
	";
	}
	
	function approval_link($uid)
	{
	return "
	<a href=\"index.php?a=admin&opcode=approve_user&uid={$uid}\" title='Одобрить пользователя' alt='Одобрить'><img src=\"<#IMG_DIR#>/approve.gif\"></a>
	";   
	}
	
	function users_list($data)
	{
	return "
	<h2><center>Пользователи</h2>
	<table align='center' width='50%'>
	<tr><th>ID<th>Имя<th>Правка<th>Удаление<th>Одобрение</tr>
	{$data}
	</table>
	";
	}
	
	function file_row($data)
	{
	return "
	<tr>
	<td width='5%' class='cell2'>
	<center>{$data['fid']}</center>
	<td class='cell3' width='15%'>
	<center>{$data['date']}</center>
	<td class='cell3' width='30%'>
	<center>{$data['desc']}{$data['vip_note']}</center>
	<td class='cell3'>
	<center><a href='{$data['link']}'>{$data['file']}</a></center>
	<td class='cell3' width='5%'>
	<center>
	<a onclick='if (window.confirm(\"Редактировать файл?\")) window.location.href=\"index.php?a=admin&opcode=edit_file&fid={$data['fid']}\"' href='#' title='Редактировать файл' alt='Редактирование'><img src=\"<#IMG_DIR#>/edit.gif\"></a>&nbsp;
	</center>
	<td class='cell3' width='5%'>
	<center>
	<a onclick='if (window.confirm(\"Удалить файл?\")) window.location.href=\"index.php?a=admin&opcode=delete_file&fid={$data['fid']}&fname={$data['link']}\"' href='#' title='Удалить файл' alt='Удаление'><img src=\"<#IMG_DIR#>/delete.gif\"></a>
	</center>
	</td>
	</tr>
	";
	}
	
	function files_list($rows)
	{
	return "
	<table align='center' width='75%'>
	<tr>
	<th>ID
	<th>Загружен
	<th>Описание
	<th>Файл
	<th><th>
	</tr>
	{$rows}
	</table><br><br>
	";
	}
	
	function add_file_form($cfg)
	{
	return "
	<script>
	function validate()
	{
	if (addfile.desc.value.length > 255) {window.alert('Длина описания не может превышать 255 символов!'); return false; } else return true;
	}
	</script>
	<div class='desc'>Загрузите файл
	<br />
	Максимальный размер файла: {$cfg['upload_max_size']} Кбайт<br>
	Допустимые расширения: {$cfg['upload_extensions']}
	</div>
	<br><br>
	<form enctype='multipart/form-data' name='addfile' method='post' action='index.php'>
	<input type='hidden' name='opcode' value='do_add_file'>
	<input type='hidden' name='a' value='admin'>
	Описание:<br /><input type='text' size='45' name='desc'><br /><br />
	Файл:<br /><input type='file' name='file'><br /><br />
	<input type='checkbox' name='vip'>&nbsp;Только для друзей
	<input type='button' onclick='if (validate()) addfile.submit();' value='Загрузить'>
	</form>
	";
	}
	
	function add_file_link()
	{
	return "
	<center><a href='index.php?a=admin&opcode=add_file'>[Загрузить файл]</a></center><br><br>
	";
	}
	
	function edit_file_form($data)
	{
	
	return "
	<script>
	function validate()
	{
	if (editfile.desc.value.length > 255) {window.alert('Длина описания не может превышать 255 символов!'); return false; } else return true;
	}
	</script>
	<div class='desc'>Измените описание файла или уровень доступа к нему
	</div><br><br>
	<form name='editfile' method='post' action='index.php'>
	<input type='hidden' name='opcode' value='do_edit_file'>
	<input type='hidden' name='a' value='admin'>
	<input type='hidden' name='fid' value='{$data['fid']}'>
	Описание:<br /><input type='text' size='45' name='desc' value='{$data['desc']}'><br /><br />
	<input type='checkbox' name='vip' {$data['sel']}>&nbsp;Только для друзей
	<input type='button'  onclick='if (validate()) editfile.submit();' value='Сохранить'>
	</form>
	";
	
	}


	function cblox_row($data)
	{
	return "
	<tr>
	<td width='5%' class='cell2'><center>{$data['bid']}</center>
	<td width='10%' class='cell3'><center>{$data['name']}</center>
	<td width='10%' class='cell3'><center>{$data['title']}</center>
	<td width='65%' class='cell3'>{$data['content']}
	<br>
	<br><i>Уровень доступа: <b>{$data['perm']}</b></i>
	<td class='cell3' width='5%'>
	<center>
	<a onclick='if (window.confirm(\"Редактировать блок?\")) window.location.href=\"index.php?a=admin&opcode=edit_cblock&bid={$data['bid']}\"' href='#' title='Редактировать блок' alt='Редактирование'><img src=\"<#IMG_DIR#>/edit.gif\"></a>&nbsp;
	</center>
	</tr>
	";
	}

	function add_cblock_form()
	{
	return "
	<script>
	function validate()
	{
	if (addcblock.content.value.length > 20480) {window.alert('Длина содержимого не может превышать 20 килобайт!'); return false; } else return true;
	}
	</script>
	<div class='desc'>Заполните информацию о блоке</div>
	<br><br>
	<form name='addcblock' method='post' action='index.php'>
	<input type='hidden' name='opcode' value='do_add_cblock'>
	<input type='hidden' name='a' value='admin'>
	Имя:<br /><input type='text' size='45' name='name'><br />
	Заголовок:<br /><input type='text' name='title' size='45'><br /><br /><br />
	Содержимое:<br /><textarea name='content' cols='70' rows='20'></textarea><br><br>
	Уровень доступа:<br>
	<input type='radio' name='perm' value='0' checked>&nbsp;Для всех&nbsp;&nbsp;&nbsp;
	<input type='radio' name='perm' value='1'>&nbsp;Только для друзей&nbsp;&nbsp;&nbsp;
	<input type='radio' name='perm' value='2'>&nbsp;Личное&nbsp;&nbsp;&nbsp;
	<br /><br />
	<input type='button' onclick='if (validate()) addcblock.submit();' value='Создать блок'>
	</form>
	";
	}

	
	function edit_cblock_form($data)
	{
	return "
	<script>
	function validate()
	{
	if (editcblock.content.value.length > 20480) {window.alert('Длина содержимого не может превышать 20 килобайт!'); return false; } else return true;
	}
	</script>
	<div class='desc'>Заполните информацию о блоке</div>
	<br><br>
	<form name='editcblock' method='post' action='index.php'>
	<input type='hidden' name='opcode' value='do_edit_cblock'>
	<input type='hidden' name='a' value='admin'>
	<input type='hidden' name='bid' value=\"{$data['bid']}\">
	Имя:<br /><input type='text' size='32' name='name' value=\"{$data['name']}\"><br />
	Заголовок:<br /><input type='text' name='title' value=\"{$data['title']}\" size='32'><br /><br /><br />
	Содержимое:<br /><textarea name='content' cols='70' rows='20' nowrap='off'>{$data['content']}</textarea><br><br>
	Уровень доступа:<br>
	<input type='radio' name='perm' value='0' {$data['perm0']}>&nbsp;Для всех&nbsp;&nbsp;&nbsp;
	<input type='radio' name='perm' value='1' {$data['perm1']}>&nbsp;Только для друзей&nbsp;&nbsp;&nbsp;
	<input type='radio' name='perm' value='2' {$data['perm2']}>&nbsp;Личное&nbsp;&nbsp;&nbsp;
	<br /><br />
	<input type='checkbox' name='delete_cblock'>&nbsp;Удалить блок?
	<br /><br />
	<input type='button' onclick='if (validate()) editcblock.submit();' value='Редактировать блок'>
	</form>
	";
	}

	
	function cblox_list($rows)
	{
	return 	"
	<table align='center' width='75%'>
	<tr>
	<th>ID
	<th>Имя
	<th>Заголовок
	<th>Содержимое
	<th>Правка
	</tr>
	{$rows}
	</table><br><br>
	";
	}

	function add_cblock_link()
	{
	return "
	<a href='index.php?a=admin&opcode=add_cblock'>[Добавить блок]</a><br /><br />
	";
	}
	
	function edit_wrapper_form($data,$skin,$blocklist)
	{
	return "
	<div class='note'>Здесь вы можете отредактировать основной шаблон блога.<br>Для включения в вывод страницы блоков используйте шаблоны подстановки, список которых приведён ниже.</div><br>
	<form name='edit_wrapper' action='index.php' method='post'>
	<input name='a' value='admin' type='hidden'>
	<input name='skin' value='$skin' type='hidden'>
	<input name='opcode' value='do_edit_wrapper' type='hidden'>
	<table>
	<td width='70%'><center>	
	<textarea name='content' cols=100 rows=50>{$data}</textarea>
	</center>
	<td>{$blocklist}
	</table>
	<br><br>
	<center>
	<input type='submit' value='Сохранить шаблон'>
	</center>
	</form>
	";
	}
	
	function blocklist_row($name,$cont)
	{
	return "
	<b>{% {$name} %}</b> {$cont['desc']}
	<hr>
	";
	}

	function no_cblox()
	{
	return "
	<br><br><br><br>
	<div class='note'>Пользовательские блоки не созданы</div>
	<br><br><br><br>
	";
	}

	function no_keywords()
	{
	
	return "
	<br><br><br><br>
	<div class='note'>В базе нет ключевых слов</div>
	<br><br><br><br>
	";
	
	}
	
	function no_moods()
	{
	
	return "
	<br><br><br><br>
	<div class='note'>В базе нет настроений</div>
	<br><br><br><br>
	";
	
	}
	
	function no_files()
	{
	
	return "
	<br><br>
	<div class='note'>Нет загруженных файлов</div>
	<br><br><br><br>
	";
	
	}



}
