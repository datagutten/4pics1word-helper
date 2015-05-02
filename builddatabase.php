<?Php
//Build database for guess the shadow or mega quiz
if(!isset($argv[1]) || !isset($argv[2]))
	die("Usage: builddatabase.php [game] [language]\n");
//$game=$argv[1];
//$lang=$argv[2];
$data=file_get_contents($argv[1]);
$data=str_replace("\r",'',$data);
//                         2                   3              4             5
preg_match_all('^(level\=(.+)\nother_letters=(.*)\ncategory=(.+)\npicname=(.+))\n\n^',$data,$tasks); //other_letters\=(.+).+\n\n
//print_r($tasks);
//die();
//$db=new pdo("sqlite:$game/data/shadows.db");
$db=new pdo("sqlite:/tmp/game.db");
$q="CREATE TABLE `item` (
  `id` INT NOT NULL,
  `solution` VARCHAR(45) NULL,
  `category` VARCHAR(45) NULL,
  `picname` VARCHAR(45) NULL,
  `other_letters` VARCHAR(45) NULL,
  `display` VARCHAR(45) NULL,
  PRIMARY KEY (`id`));";
  //var_dump($q);
$db->query(str_replace("\n",'',$q));
unset($q);
foreach(array_keys($tasks[0]) as $key)
{
	//                             id      solution            category            picname            other_letters         display
	$solution=str_replace(array(' ','\''),'',$tasks[2][$key]);
	$tasks[2][$key]=$db->quote($tasks[2][$key]);
	//$solution=preg_replace('/[^A-Åa-å^]'
	$q="INSERT INTO item VALUES ($key,'$solution','{$tasks[4][$key]}','{$tasks[5][$key]}','{$tasks[3][$key]}',{$tasks[2][$key]})";
	//var_dump($q);
	//die();
	echo $q."\n";
	if(!$db->query($q))
	{
		print_r($db->errorInfo());
		break;
	}
}

//$db->query("CREATE TABLE shadows (solution
//copy("/tmp/$game.db","$game/data/{$game}_{$lang}.db");
rename("/tmp/game.db",$argv[2]);
//unlink("/tmp/game.db");