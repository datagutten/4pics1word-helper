<?Php
require 'photodata.php';
$debug=false;

if(!isset($gui)) //For running on command line
{
	
	if(!isset($argv[1]) || !isset($argv[2]))
		die("Command line usage:\nphp 4pics1word.php [available letters] [number of letters in solution word]\n\nExample: php 4pics1word.php xyumquvcmoza 6\n\n");
	$queryletters=strtoupper($argv[1]);
	$letters=$argv[2];
}
$query=str_split($queryletters);

if(!is_numeric($letters))
	die('Invalid number of letters');

foreach ($words[$letters] as $key=>$word)
{
	for($pos=0; $pos<$letters; $pos++)
	{
		if(array_search(substr($word,$pos,1),$query)===false)
		 continue 2;
		
	}
	$possibles[$key]=$word;
}
if(!isset($gui))
{
	foreach ($possibles as $key=>$possible)
	{
		echo "$possible";
		if($debug)
		{
			echo " ($key)\n";
			print_r($tasks[$key]);
		}
		else
			echo "\n";
	}
}
?>