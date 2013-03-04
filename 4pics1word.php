<?Php
//$queryletters='bvcuetahcxpa';
$query=str_split($queryletters);
//$query=explode(',',$queryletters);
//$letters=5;
if(!is_numeric($letters))
	die('Invalid number of letters');

include $letters."word.php";
foreach ($words[$letters] as $key=>$word)
{
	for($pos=0; $pos<$letters; $pos++)
	{
		if(array_search(substr($word,$pos,1),$query)===false)
		 continue 2;
		
	}
	$possibles[$keys[$letters][$key]]=$word;
}
if(!isset($gui))
{
foreach ($possibles as $key=>$possible)
{
	echo "$possible ($key)\n";
}
}
?>