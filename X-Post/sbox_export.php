<?php

class sbox_export
{

function run()
{
global $std;

switch ($std->input['opcode'])
{
default:
case 'view': $this->view(); break;
case 'view_all': $this->view_all(); break;
}

$this->finish();
}

function view()
{
global $skin,$mysql,$cfg;
$mysql->connect();
$q=$mysql->query("SELECT * FROM sbox c,users u WHERE c.uid=u.uid  ORDER BY `date` desc LIMIT {$cfg['sbox_rows_limit']}");
while ($row = mysql_fetch_assoc($q))
{
	$rows[]=$this->render_row($row);
	
}
$rows ? $rows = implode($skin->divisor(),$rows) : $rows = $skin->no_messages();
	
$this->html.=$skin->msg_list($rows);
}


function view_all()
{
global $skin,$mysql,$cfg;
$mysql->connect();
$q=$mysql->query("SELECT * FROM sbox c,users u WHERE c.uid=u.uid  ORDER BY `date` desc");
while ($row = mysql_fetch_assoc($q))
{
	$rows[]=$this->render_row($row);
}
$count = count($rows);
$rows ? $rows = implode($skin->divisor(),$rows) : $rows = $skin->no_messages();
	
$this->html.=$skin->msg_list_arch($rows);

}

function render_row($data)
{
global $skin,$user,$cfg,$std;

if ($user->is_admin)
{
	$data['admin_links'] = $skin->admin_links($data);
}
$data['date'] = date($cfg['dateformat'],$data['date']);
($data['uid']!=0) ? $data['author_link'] = $skin->author_link($data) : $data['author_link'] = $skin->author_link_guest();
$data['text'] = $std->parse_post($data['text']);
return $skin->msg($data);

}

function finish()
{
global $cfg,$std;
//обработаем путь до директории изображении, т.к. в этом модуле отключён основной враппинг
$this->html=str_replace('<#IMG_DIR#>',$cfg['skin_dir'].'/'.$std->skin_read(),$this->html);
}

}

?>
