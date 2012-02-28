<?php

class searchbox

{

function run()
{
global $skin;


$this->html=$skin->search_form($data);

$this->html=$skin->module($this->html);
}

}


?>
