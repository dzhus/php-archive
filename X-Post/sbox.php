<?php

class sbox
{

function run()
{
global $std,$skin;

$std->title='SpeakerBoxx';

	switch ($std->input['opcode'])
	{
	case 'msg': $this->view_msg(); break;
	case 'arch' : $this->view_arch(); break;
	case 'add': $this->add_msg(); break;
	case 'del': $this->del_msg(); break;
	default: break;
	case 'all': $this->view_all(); $this->finish(); return;
	}
$this->view();

$this->finish();
}

function view()
{
global $skin,$cfg;
$this->html.=$skin->makeframe('');
$this->sbox_form();
$this->html.=$skin->status($cfg['sbox_rows_limit']);

}


function view_all()
{
global $skin,$mysql;

$this->html.=$skin->makeframe_archive('_all');
$this->sbox_form();
$mysql->connect();
$q = $mysql->query("SELECT * FROM sbox");
$count = mysql_num_rows($q);
$this->html.=$skin->status_viewall($count);
}


function add_msg()
{
global $mysql,$user,$std,$cfg;

if (!$std->input['msg'])
{
    return;
}

//kill nasty duping bug
if ($_COOKIE['sbox_last_msg'] == $std->input['msg']) return;

if ($_COOKIE['sbox'])
{
    $this->html.=$std->error('no_flood_sbox',0,0);
    return;
} else SetCookie('sbox','I love Ira!',time()+$cfg['sbox_flood_timeout']);


if ($std->input['msg']) $text = $std->input['msg']; else { $this->html.=$std->error('no_input'); return; }
$user->uid ? $uid = $user->uid : $uid = 0;
$mysql->connect();
$mysql->query("INSERT INTO sbox (uid,date,text) VALUES ({$uid},".time().",'{$text}');");
SetCookie('sbox_last_msg',$text,time()+19031989);

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
$mysql->query("DELETE FROM sbox WHERE `mid`={$mid}");

$this->html.=$std->success('sbox_msg_deleted');

}


function sbox_form()
{
global $skin;
$this->html.=$skin->sbox_form();

}

function finish()
{
    global $skin;
    $this->html=$skin->sbox_header().$this->html;
    $this->html=$skin->module($this->html);
}


}


?>
