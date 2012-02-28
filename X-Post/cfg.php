<?php

$cfg = array
(
"dbhost" => "localhost",
"dbuser"=> "geekclru_blog",
"dbname"=> "geekclru_blog",
"dbpass"=> "fl0ff!",

"default_modname" => "entry",

"skin_dir" => "skins",
"root_dir" => "/home/g/geekclru/sphinx/pubic_html/",
"upload_dir" => "uploads",

"blog_url" => "http://sphinx.net.ru",

"from_mail" => "noreply@sphinx.net.ru",

//избегайте < и > в blog_name
"blog_name" => "Sphinx.Net.Ru",

"flood_timeout" => 30,

"short_dateformat" => "G:i:s",
"dateformat" => "j.n.y в G:i:s",
"birth_dateformat" => "j.n.Y",

"max_photo_size" => 500,
"max_photo_w" => 250,
"max_photo_h" => 250,

"owner_box_photo_w" => 125,
"owner_box_photo_h" => 125,

"photo_w" => 125,
"photo_h" => 125,
"photo_upload_extensions"=>"jpeg|jpg|gif|png",

"rss_limit" => 10,
"rss_post_cut" => 700,
"rss_cachetime" =>60,

"sbox_rows_limit" => 20,
"sbox_last_rows_limit" => 3,
"sbox_last_chars_limit" => 25,
//seconds!
"sbox_flood_timeout" => 10,

"upload_max_size"=>50000,
"upload_extensions"=>"jpeg|jpg|gif|png|txt|gz|lyx|tar|avi|zip|rar",

//minutes!
"online_timeout"=>15

);

?>
