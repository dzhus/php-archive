<?php
//GC Card
//UPD: 24.05.2005

//самый главный класс

class card {
   
function init()
{

  
require "cfg.php";

$this->ROOT_PATH=$ROOT_PATH;
$this->SCRIPT_PATH=$SCRIPT_PATH;

//подключим IPB SDK
require_once $this->SDK_PATH."ipbsdk_class.inc.php";
$this->SDK =& new IPBSDK();

//теперь подумаем с шрифтами:
$this->fonts_list = array (
"cour" => array 
          (
          "file"=>"cour.ttf",
          "wrap"=>30,
          "wrapA"=>20,
          "size"=>9
          ),
          

"courb" => array 
          (
          "file"=>"courbd.ttf",
          "wrap"=>30,
          "wrapA"=>19,
          "size"=>10
          ),
"mt" => array
        (
        "file"=>"mt.ttf",
        "wrap"=>30,
        "wrapA"=>22,
        "size"=>12
        ),
"agit" => array
        (
        "file"=>"agit.ttf",
        "wrap"=>30,
        "wrapA"=>20,
        "size"=>11
        ),
"myr" => array
        (
        "file"=>"myriad.ttf",
        "wrap"=>32,
        "wrapA"=>22,
        "size"=>11
        ),

"lucid" => array
        (
        "file"=>"lucid.ttf",
        "wrap"=>30,
        "wrapA"=>20,
        "size"=>10
        ),
"bauhs" => array
        (
        "file"=>"bauhs.ttf",
        "wrap"=>28,
        "wrapA"=>20,
        "size"=>11
        ),
"arvigo" => array
        (
        "file"=>"arvigo.ttf",
        "wrap"=>33,
        "wrapA"=>22,
        "size"=>14
        ),
"techno" => array
        (
        "file" => "techno.ttf",
        "wrap"=>30,
        "wrapA"=>25,
        "size"=>14
        ),
     
        

        );


//и фон подберём
$this->bg_list = array (
"str1" => array (
"std" => "striped1.jpg",
"blue" => "striped1blue.jpg",
"green" => "striped1green.jpg",
"red" => "striped1red.jpg",
"grey" => "striped1grey.jpg"
),
"plain1" => array (
"std" => "plain1.jpg",
"blue" => "plain1blue.jpg",
"green" => "plain1green.jpg",
"red" => "plain1red.jpg",
"grey" => "plain1grey.jpg"
)
);

//CONFIGURABLE:
$this->topmargin=36;
$this->leftmargin=10;
$this->basepadding=9;

}

function make_card_prepare()
{
//дёргаем гик-код
require($this->SCRIPT_PATH."getcode.php");
$this->code=getcode($this->minfo['id']);

if ($_GET['bg']) $this->bg = $this->bg_list[$_GET['bg']][$_GET['color']]; else $this->bg = $this->bg_list['str1']['std'];
$this->bg = $this->ROOT_PATH."card/".$this->bg;
//echo $this->bg;

if ($_GET['font']) $this->font = $this->fonts_list[$_GET['font']]['file']; else $this->font = $this->fonts_list['mt'];
$this->fontsize=$this->fonts_list[$_GET['font']]['size'];
//путь до шрифта
$this->font = $this->ROOT_PATH."font/".$this->font;
//echo $this->font;
//cut у WordWrap()
$this->wrapcut = $this->fonts_list[$_GET['font']]['wrap'];
$this->wrapcutA= $this->fonts_list[$_GET['font']]['wrapA'];


//приступаем к настройкам:
($_GET['use_avatar']) ? $this->use_avatar = 1 : $this->use_avatar = 0;
($_GET['use_icq']) ? $this->use_icq = 1: $this->use_icq = 0;
($_GET['use_email']) ? $this->use_email = 1 : $this->use_email = 0;

}

//получаем инфу о юзере и аватар
function get_member_info()
{
$this->minfo=$this->SDK->get_advinfo();
$this->current_id=$this->minfo['id'];

$this->minfo = $this->SDK->get_advinfo($_GET['id']);

}

function prepare_avatar()
{
if ($this->use_avatar)
{
      $this->avatar = $this->SDK->get_avatar($this->minfo['id']);
      if ($this->avatar)
      {
         //выдергиваем урл аватара
         $pattern = "/.*(http:.*(jpg|jpeg|gif)).*/";
         $replace = "$1";
         $this->avatar=preg_replace($pattern,$replace,$this->avatar);
         $this->avasize=getimagesize($this->avatar);

      }
      else
      {
         $this->use_avatar=0;
      }
}

}

function make_card_avatar()
{
   
header("Content-type: image/jpeg");
//создаём основу
$img = imagecreatefromjpeg($this->bg);

//создаём аватару
switch ($this->avasize[2])
{
  case 1: $ava = imagecreatefromgif($this->avatar); continue;
  case 2: $ava = imagecreatefromjpeg($this->avatar); continue;
  case 3: $ava = imagecreatefrompng($this->avatar); continue;
}


//впечатываем аватару
imagecopy($img,$ava,$this->leftmargin,$this->topmargin-$this->fontsize,0,0,$this->avasize[0],$this->avasize[1]);

$black=ImageColorAllocate($img,0,0,0);  


//впечатываем ник
//задаём $namebox, чтобы потом по границе ника ориентироваться при впечатке мыла
$namebox=imagettftext($img, $this->fontsize+2,0,$this->leftmargin,$this->topmargin+$this->avasize[1]+$this->basepadding,$black,$this->font,$this->minfo['name']);  



//разбиваем гик-код 
$str=$this->code;
$str=WordWrap($str,$this->wrapcutA);

//бокс гик-кода:
$gcodebox=imagettfbbox($this->fontsize+1,0,$this->font,$str);
//может, мыло стоит опустить пониже, дабы не перекрывало гик-код?
$mailbox=imagettfbbox($this->fontsize+1,0,$this->font,$this->minfo['email']);
if ($mailbox[1]>$gcodebox[1]) $namebox[1]=$gcodebox[1];

//мыло и icq
if (($this->use_email) && ($this->minfo['email'])) $mailbox=imagettftext($img, $this->fontsize-1,0,$this->leftmargin,$namebox[1]+$this->basepadding*2,$black,$this->font,$this->minfo['email']);
if (!(($this->use_email) && ($this->minfo['email']))) $mailbox=$namebox;
if (($this->use_icq) && ($this->minfo['icq_number'])) $icqbox=imagettftext($img, $this->fontsize-1,0,$this->leftmargin,$mailbox[1]+$this->basepadding*2,$black,$this->font,$this->minfo['icq_number']);

//по чему выравнивать гик-код?
 if (($namebox[2]<$this->avasize[0]) OR ($gcodebox[1]<$this->avasize[1]))
 {
    $namebox[2]=$this->avasize[0];
 }
//и впечатываем гик-код
//  echo $str;
$pos=imagettftext($img, $this->fontsize+2, 0, $this->leftmargin+$namebox[2]+$this->basepadding*2,$this->topmargin, $black, $this->font, $str);


//вывод, завершение работы
imagejpeg($img,'',90);
imagedestroy($img);
  
}

function make_card_no_avatar()
{
   
header("Content-type: image/jpeg");
//создаём основу
$img = imagecreatefromjpeg($this->bg);

$black=ImageColorAllocate($img,0,0,0);  

//$pos - границы бокса последнего элемента
$pos = array();

//впечатываем ник
$pos=imagettftext($img, $this->fontsize+3,0,$this->leftmargin,$this->topmargin,$black,$this->font,$this->minfo['name']);  

//разбиваем и впечатываем гик-код
$str=$this->code;
$str=WordWrap($str,$this->wrapcut);
//  echo $str;
$pos=imagettftext($img, $this->fontsize+3, 0, $this->leftmargin,$this->topmargin+$pos[0]+$this->basepadding*2, $black, $this->font, $str);

//мыло и icq
if (($this->use_email) && ($this->minfo['email'])) { $pos=imagettftext($img, $this->fontsize,0,$this->leftmargin,$pos[1]+$this->basepadding*2,$black,$this->font,$this->minfo['email']);
if (($this->use_icq) && ($this->minfo['icq_number'])) $pos=imagettftext($img, $this->fontsize,0,$this->leftmargin,$pos[1]+$this->basepadding,$black,$this->font,$this->minfo['icq_number']);
}
else
if (($this->use_icq) && ($this->minfo['icq_number'])) $pos=imagettftext($img, $this->fontsize,0,$this->leftmargin,$pos[1]+$this->basepadding*2,$black,$this->font,$this->minfo['icq_number']);
 


//вывод, завершение работы
imagejpeg($img,'',100);
imagedestroy($img);
  
}

//сохранение карты
function save_card()
{
 $settings=array(
 'avatar' => $this->use_avatar,
 'font' => $_GET['font'],
 'icq' => $this->use_icq,
 'email' => $this->use_email,
 'bg' => $_GET['bg'],
 'color'=> $_GET['color']
 
 );
 $settings=serialize($settings);
 
 mysql_connect($this->dbhost,$this->dbuser,$this->dbpass);
 mysql_db_query($this->dbname, "UPDATE ibf_members SET gcard='".$settings."' WHERE id='".$this->current_id."';");
 echo "<center><b>Сохранено!</b></center>";
 
}

//восстановление карты
function get_card()
{
 mysql_connect($this->dbhost,$this->dbuser,$this->dbpass);
  $q=mysql_db_query($this->dbname, "SELECT * FROM ibf_members WHERE id='".$this->minfo['id']."';");
  $q=mysql_fetch_assoc($q);

  $settings=unserialize($q['gcard']);

  $this->use_avatar=$settings['avatar'];
  $this->use_icq=$settings['icq'];
  $this->use_email=$settings['email'];
  $this->bg = $this->bg_list[$settings['bg']][$settings['color']];
  $this->bg = $this->ROOT_PATH."card/".$this->bg;
//  echo $this->bg;
  $this->font = $this->fonts_list[$settings['font']]['file'];
  $this->fontsize = $this->fonts_list[$settings['font']]['size'];
  //путь до шрифта
  $this->font = $this->ROOT_PATH."font/".$this->font;

//  echo $this->font;
  //cut у WordWrap()
  $this->wrapcut = $this->fonts_list[$settings['font']]['wrap'];
  $this->wrapcutA= $this->fonts_list[$settings['font']]['wrapA'];

  require($this->SCRIPT_PATH."getcode.php");
  $this->code=getcode($q['id']);
  
  }



//сервисные функции запуска

//запуск создания новой карты через форму
function run_makecard()
{
           $this->get_member_info();
           $this->make_card_prepare();
           $this->prepare_avatar(); 
           switch ($this->use_avatar)
           {
                 case 1:$this->make_card_avatar();continue;
                 case 0:$this->make_card_no_avatar();continue;
           }
}

//запуск сохранения карты в базе данных
function run_savecard()
{
         $this->get_member_info();
         $this->make_card_prepare();
         $this->save_card();
}

//запуск восстановления карты из базы данных
function run_getcard()
{
         $this->get_member_info();
         $this->get_card();
         $this->prepare_avatar();
         switch ($this->use_avatar)
           {
                 case 1:$this->make_card_avatar();continue;
                 case 0:$this->make_card_no_avatar();continue;
           }
}

function dispatch()
{
   $this->init(); 
   switch ($_GET['card_opcode'])
   {
      case 10:
           {   
           $this->run_makecard();
            continue;
           }
      case 20:
           {
           $this->run_savecard();
           continue;
           }
      case 30:
           {
           $this->run_getcard();
           continue;
           }
}

}

}

$card = new card;
$card->dispatch();

?>