<?php

class skin_searchbox
{

function search_form($data)
{
return "
<form name='search' action='index.php' method='GET'>
Содержимое:<br><center><input name='contents' size=15>
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

?>