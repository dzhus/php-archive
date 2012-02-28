<?php

class rss
{

function startElement($parser,$name,$attrs)
{

//what kind of stuff do we have?

switch ($name)
{
case 'channel': 
	$this->items = array();
	break;

case 'item':
	$this->item = array();
	$this->in_item = true;
	break;
	
default:
	break;
}

}

function endElement($parser,$name)
{

if ($name=='item')

{

$this->items[] = $this->item;

$this->in_item = false;

}

}

function charData($parser,$data)
{


if (!($this->in_item)) return;

switch ($this->tag)
{

case 'title':
	$this->item['title'] = $data;
	break;
case 'link':
	$this->item['link'] = $data;
	break;
case 'description':
	$this->item['desc'] = $data;
	break;
case 'pubDate':
	$this->item['date'] = getdate($data);
	break;
default:
	echo $data."<BR>";
	break;
}	

}


function process_channel($feed)
{
$file = join('',file($feed['url']));
$parser = xml_parser_create();

xml_set_object($parser,$this);

//xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,1);
xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);

xml_set_element_handler($parser,'startElement','endElement');
xml_set_character_data_handler($parser,'charData');

xml_parse($parser,$file);

xml_parser_free($parser);

print_r($this->items);
}

function run()
{
global $mysql,$std,$cfg;

$mysql->connect();

$q = $mysql->query("SELECT * FROM feeds");

while ($feed = mysql_fetch_assoc($q))
{
$this->process_channel($feed);
}

}


}






   
   

?>