<?php

class file
{

function run()
{
global $std;


switch ($std->input['opcode'])
{
default:
case 'all': $this->view_all();break;
case 'get': $this->get_file();break;
}

$this->finish();
}



function view_all()
{

global $skin,$mysql,$user,$cfg;

$mysql->connect();
$q = $mysql->query("SELECT * FROM files ORDER BY fid ASC");
if (mysql_num_rows($q)==0)
{
	$this->html.=$skin->no_files();
	return;
}
while ($row = mysql_fetch_assoc($q))
{
//let's hide our leet files from guests, ага
if (!(!$user&&$row['vip']))
	{
		$row['date'] = date($cfg['dateformat'],$row['date']);
		$row['link'] = $cfg['upload_dir'].'/'.$row['file'];
		$files[] = $skin->file_row($row);
	}
}
$this->html.=$skin->files_list(@implode("",$files));
}


function get_file()
{
global $std,$user,$cfg,$mysql;

$fid = intval($std->input['fid']);
if (!$fid)
{
$this->html.=$std->error('no_input');
return;
}

$mysql->connect();
$q = $mysql->query("SELECT * FROM files WHERE `fid`={$fid}");
$data = mysql_fetch_assoc($q);

if ($data['vip']&&!$user)
{
$this->html.=$std->error('vip_file');
return;
}
header("Location: ".$cfg['upload_dir'].'/'.$data['file']);


}


function finish()
{
    global $skin;
    $this->html=$skin->module($this->html);
}

}

?>
