<?Php
require 'class.php';
//$game='4pics1word';
$pics=new pics();

if(strpos($argv[1],'4 Pics 1 Word')!==false)
	$game='4pics1word';
elseif(strpos($argv[1],'Icomania')!==false)
	$game='icomania';
elseif(strpos($argv[1],'Pic Combo')!==false)
	$game='piccombo';
elseif(strpos($argv[1],'Gjett skyggen')!==false)
	$game='shadows';


if(!$pics->selectgame($game))
	die("Invalid game: $game\n");

$path='unpack';
shell_exec("unzip \"{$argv[1]}\" -d $path");
$pattern_images=array('4pics1word'=>'Payload/4 Pics 1 Word.app/_*.jpg','icomania'=>'Payload/Icomania.app/_*.png','piccombo'=>'Payload/Pic Combo.app/_*.jpg','shadows'=>'Payload/Shadows.app/[A-Z]*.png');
$pattern_gamedata=array('4pics1word'=>'Payload/4 Pics 1 Word.app/itemData.db','icomania'=>'Payload/Icomania.app/icomania_*.json','piccombo'=>'Payload/Pic Combo.app/itemdata.json');

if(isset($pattern_images[$game]))
{
	$files=glob($path.'/'.$pattern_images[$game]);
	//print_r($files);
	if(!file_exists($pics->dir_images.'/app'))
		mkdir($pics->dir_images.'/app',0777,true);

	foreach($files as $file)
	{
		rename($file,$pics->dir_images.'/app/'.basename($file));
	}
}
if(isset($pattern_gamedata[$game]))
{
	$files_data=glob($path.'/'.$pattern_gamedata[$game]);
	if(!file_exists($pics->dir_gamedata))
		mkdir($pics->dir_gamedata,0777,true);
	foreach($files_data as $file)
	{
		rename($file,$to=$pics->dir_gamedata.'/'.basename($file));
		//echo $to."\n";
	}
}
if($game=='shadows')
{
	if(!file_exists($pics->dir_gamedata))
		mkdir($pics->dir_gamedata,0777,true);

	foreach(glob($path.'/Payload/Shadows.app/*lproj') as $langdir)
	{
		$lang=substr($langdir,-8,2);
		//rename($lang,$pics->dir_gamedata."/levels_$lang.txt");
		if(!file_exists($pics->dir_gamedata.'/levels_'.$lang.'.db'))
		shell_exec("php builddatabase.php \"$langdir/levels.txt\" \"{$pics->dir_gamedata}/levels_$lang.db\"");
	}
}
//print_r($files);
//http://php.net/manual/en/function.rmdir.php#115598
if (PHP_OS === 'Windows')
{
    exec("rd /s /q {$path}");
}
else
{
    exec("rm -rf {$path}");
}