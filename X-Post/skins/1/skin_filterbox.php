<?php

class skin_filterbox
{

function filter_form($data)
{
return "
<form name='filter' action='index.php' method='GET'>
<center><small>Поиск записей</small><br>
Содержимое:<br><input name='contents' size=15>
<input type='submit' value='Поиск'></center>
</form>
";
}

function module($data)
{
return "
<div class='module'>
<div class='subheading'>
Поиск</div>
{$data}
</div>
";

}

}

$skin = new skin_filterbox;
?>