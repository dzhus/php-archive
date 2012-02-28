<?php

//XML2PHP bridge
//GCUFBL
//upd 28.07.2005



function parse($str)
{
   	$str=str_replace("plus","+",$str);
   	$str=str_replace("minus","-",$str);
   	$str=str_replace("que","?",$str);

return($str);
}

function check_sub($str)
{

if ((!strstr($str,"sub")) && ($str!="b")) return true; else return false;

}

function makedef($xml)
{
$file=join("",file($xml));

$parser=xml_parser_create();
xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
xml_parse_into_struct($parser,$file,$vals,$tags);
xml_parser_free($parser);


   foreach ($vals as $tag)
   {
     
//     if ($tag['type']=='complete' || $tag['type']=='open') 
//     if ($tag['type']!='close') 
{
     

	$tag['tag']=parse($tag['tag']);
     //согласно уровню тега распихием теги по массиву...
     switch ($tag['level'])
     {
     case 2: $cur_tag[2]=$tag['tag']; continue;
     case 3: $cur_tag[3]=$tag['tag']; if (check_sub($tag['tag'])) $def[$cur_tag[2]][$cur_tag[3]]=$tag['value'];  continue;
     case 4: $cur_tag[4]=$tag['tag']; $def[$cur_tag[2]][$cur_tag[3]][$cur_tag[4]]=$tag['value']; continue;
  
}
     }
   }


return($def);
}
//$def сгенерирован
   



?>