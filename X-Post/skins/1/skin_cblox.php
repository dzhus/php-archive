<?php

class skin_cblox
{
  
	
function block($data)
{
return "
<div class='module'>
<div class='subheading'>
{$data['title']}
</div>
{$data['content']}
</div>
";
}


}


?>
