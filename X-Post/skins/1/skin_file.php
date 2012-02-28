<?php

class skin_file
{

	function module($data)
	{
	return "
	<div class='module'>
	{$data}
	</div>
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
	<center>{$data['desc']}</center>
	<td class='cell3'>
	<center><a href='{$data['link']}'>{$data['file']}</a></center>
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
	</tr>
	{$rows}
	</table><br><br>
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

?>
