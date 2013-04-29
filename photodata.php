<?Php
//Select the correct data file for the game
switch($game)
{
	case 'icomania': $file='data/icomania/icomania_en.json'; break;
	case '4pics1word': $file='data/4pics1word/photodata.txt'; break;
	default: die("$game is not a supported game.\n");
}
if(!file_exists($file))
	die("The data file $file for $game could not be found\n");


$data=file_get_contents($file); //Read the file with the image information
$tasks=json_decode($data,true); //Decode the json
foreach($tasks as $key=>$task)
{
	$solutions[$key]=$task['solution']; //Make a searchable array of the solutions
	$len=strlen($task['solution']); //Find the length of the word
	$words[$len][$key]=$task['solution']; //Add the word to an array with the length as the key
}
?>