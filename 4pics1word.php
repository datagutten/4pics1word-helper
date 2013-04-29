<?Php
$debug=false;
if(!isset($gui)) //For running on command line
{
	if(!isset($argv[1]) || !isset($argv[2]) || !is_numeric($argv[2]) || !isset($argv[3])) //Check the parameters
		die("Command line usage:\nphp 4pics1word.php [available letters] [number of letters in solution word] [4pics1word|icomania]\n\nExample: php 4pics1word.php xyumquvcmoza 6 4pics1word\n\n");
	$queryletters=strtoupper($argv[1]);
	$letters=$argv[2];
	$game=$argv[3];
}
else
	$game='4pics1word'; //GUI only supports 4pics1word

if(!is_numeric($letters))
	die("Invalid number of letters\n");

$query=str_split($queryletters); //Make a searchable array of the supplied letters
require 'photodata.php'; //Get the wordlists

if(!isset($words[$letters])) //Check if there is a wordlist with the given number of letters
	die("There are no words with $letters letters.\n");



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
		echo $possible;
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