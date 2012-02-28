<?php

class chat
{

function chat()
{
global $std,$skin;

$std->title='SpeakerBoxx';

	switch ($std->input['opcode'])
	{
	default:break;
	case 'add': $this->add_msg(); break;
	case 'del': $this->del_msg(); break;
	case 'all': $this->view_all(); $this->finish(); return;
	}
$this->view();

$this->finish();
}

function view()
{
global $skin,$mysql,$cfg;
$mysql->connect();
$q=$mysql->query("SELECT * FROM chat c,users u WHERE c.uid=u.uid  ORDER BY `time` desc LIMIT {$cfg['chat_rows_limit']}");
while ($row = mysql_fetch_assoc($q))
{
	$rows[]=$this->render_row($row);
}
$rows ? $rows = implode($skin->divisor(),array_reverse($rows)) : $rows = $skin->no_messages();
	
$this->html.=$skin->msg_list($rows);
$this->chat_form();
$this->html.=$skin->status($cfg['chat_rows_limit']);

}


function view_all()
{
global $skin,$mysql,$cfg;
$mysql->connect();
$q=$mysql->query("SELECT * FROM chat c,users u WHERE c.uid=u.uid  ORDER BY `time` desc");
while ($row = mysql_fetch_assoc($q))
{
	$rows[]=$this->render_row($row);
}
$count = count($rows);
$rows ? $rows = implode($skin->divisor(),array_reverse($rows)) : $rows = $skin->no_messages();
	
$this->html.=$skin->msg_list($rows);
$this->chat_form();
$this->html.=$skin->status_viewall($count);
}

function render_row($data)
{
global $skin,$user,$cfg,$std;

if ($user->is_admin)
{
	$data['admin_links'] = $skin->admin_links($data);
}
$data['time'] = date($cfg['dateformat'],$data['time']);
($data['uid']!=0) ? $data['author_link'] = $skin->author_link($data) : $data['author_link'] = $skin->author_link_guest();
$data['text'] = $std->parse_post($data['text']);
return $skin->msg($data);

}

function add_msg()
{
global $mysql,$user,$std,$cfg;

if (!$std->input['msg'])
{
    $this->html.=$std->error('no_input',0,0);
    return;
}
if ($_COOKIE['chat'])
{
    $this->html.=$std->error('no_flood_chat',0,0);
    return;
} else SetCookie('chat','I love Ira!',time()+$cfg['chat_flood_timeout']);

if ($std->input['msg']) $text = $std->input['msg']; else { $this->html.=$std->error('no_input'); return; }
$user->uid ? $uid = $user->uid : $uid = 0;
$mysql->connect();
$mysql->query("INSERT INTO chat (uid,time,text) VALUES ({$uid},".time().",'{$text}');");

}

function del_msg()
{
global $mysql,$std,$user;
if (!intval($std->input['mid']))
{
	$this->html.=$std->error('no_input',0,0);
	return;
}

if (!$user->is_admin)
{
	$this->html.=$std->error('no_rights',0,0);
	return;
}
$mid = intval($std->input['mid']);
$mysql->connect();
$mysql->query("DELETE FROM chat WHERE `mid`={$mid}");

$this->html.=$std->success('chat_msg_deleted');

}


function chat_form()
{
global $skin;
$this->html.=$skin->chat_form();

}

function finish()
{
    global $skin;
    $this->html=$skin->chat_header().$this->html;
    echo $skin->module($this->html);
}


}

$chat = new chat;

?>