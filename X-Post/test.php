<?
$line = "Modrol stdmodrol";       
$array[][preg_replace("/(.+)\s.*/","$1",$line)]=preg_replace("/.+\s(.*)/","$1",$line);
print_r($array);
?>																			
