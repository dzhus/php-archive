<?php

class sbox_last
{

function run()
{
global $mysql,$std,$cfg,$skin;

$mysql->connect();
$q = $mysql->query("SELECT * FROM sbox c,users u WHERE c.uid=u.uid  ORDER BY `date` desc LIMIT {$cfg['sbox_last_rows_limit']}");
while ($row = mysql_fetch_assoc($q))
{
	$rows[]=$this->render_row($row);
}
$rows ? $rows = implode($skin->divisor(),$rows) : $rows = $skin->no_messages();
$this->html.=$skin->msg_list($rows);
$this->finish();
}


function render_row($data)
{
global $skin,$user,$cfg,$std;

$data['time'] = date($cfg['dateformat'],$data['date']);
($data['uid']!=0) ? $data['author_link'] = $skin->author_link($data) : $data['author_link'] = $skin->author_link_guest();
$data['text'] = $std->strip_codes($data['text']);
if (strlen($data['text'])>$cfg['sbox_last_chars_limit'])
{
$data['text'] = substr($data['text'],0,$cfg['sbox_last_chars_limit']).'...';
}
return $skin->msg($data);
}

function finish()
{
global $skin;
$this->html=$skin->module($this->html);
}

}


?>
