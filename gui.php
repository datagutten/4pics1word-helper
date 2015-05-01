<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<?Php
ini_set('display_errors',1);
error_reporting(E_ALL);
require 'class.php';

//If the request URI contains a valid game, use that
if(!isset($_POST['game']))
{
	$urigame=str_replace('/','',$_SERVER['REQUEST_URI']);
	$pics=new pics($urigame);
}
else
	$pics=new pics($_POST['game']);

?>
<title><?Php echo $pics->games[$pics->game]; ?></title>
</head>
<body>
<?php
if(isset($_POST['button']))
{
	$gui=true;
	$pics=new pics($_POST['game']);
	$queryletters=strtoupper(preg_replace('/[^a-z]/i','',$_POST['letters'])); //Remove everything that is not a-z and make the string uppercase
	$letters=$_POST['number'];

	if($possibles=$pics->possibles($queryletters,$_POST['number']))
	{
		foreach ($possibles as $key=>$task)
		{
			if($image=$pics->image($task))
				echo "<p><img src=\"$image\" />\n";
			if(isset($task['display']))
				$text=$task['display'];
			else
				$text=$task['solution'];
			echo "<h2>$text</h2></p>\n";
		}
	}
}
?>
<form id="form1" name="form1" method="post" action="">
  <p>Available letters:
    <input type="text" name="letters" id="letters" />
  </p>
  <p>Number of letters:
    <input type="text" name="number" id="number" />
  </p>
  <p>Game: 
    <select name="game" id="select">
<?Php
foreach($pics->games as $key=>$game)
{
	if(!file_exists('data/'.$key)) //Do not display games we don't have data for
		continue;
	echo "    <option value=\"$key\"";
	if($pics->game==$key)
		echo ' selected="selected"';
	echo ">$game</option>\n";
}
?>
    </select>
  </p>
  <p>
    <input type="submit" name="button" id="button" value="Submit" />
  </p>
  <p>Source code available on <a href="https://github.com/datagutten/4pics1word-helper/">github</a>.</p>
</form>
</body>
</html>