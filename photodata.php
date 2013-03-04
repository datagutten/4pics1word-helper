<?Php
if(!file_exists('photodata.txt'))
	die("photodata.txt could not be found");
$data=file_get_contents('photodata.txt'); //Read the file with the image information
$tasks=json_decode($data,true); //Decode the json
foreach($tasks as $key=>$task)
{
	$solutions[$key]=$task['solution']; //Make a searchable array of the solutions
}
?>