<?Php
require 'class.php';
	if(!isset($argv[1]) || !isset($argv[2]) || !is_numeric($argv[2]) || !isset($argv[3])) //Check the parameters
		die("Command line usage:\nphp 4pics1word.php [available letters] [number of letters in solution word] [4pics1word|icomania]\n\nExample: php 4pics1word.php xyumquvcmoza 6 4pics1word\n\n");
	$queryletters=strtoupper($argv[1]);

if(!is_numeric($argv[2]))
	die("Invalid number of letters\n");

$pics=new pics($argv[3]);
$possibles=$pics->possibles($argv[1],$argv[2]);

foreach ($possibles as $key=>$possible)
	echo $possible['solution']."\n";

?>