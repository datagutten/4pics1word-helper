<?Php
if(!file_exists('photodata.txt'))
	die("photodata.txt could not be found");
$data=file_get_contents('photodata.txt'); //Read the file with the image information
$tasks=json_decode($data,true); //Decode the json
foreach($tasks as $key=>$task)
{
	$solutions[$key]=$task['solution']; //Make a searchable array of the solutions
	$len=strlen($task['solution']); //Find the length of the word
	$words[$len][$key]=$task['solution']; //Add the word to an array with the length as the key
}
?>