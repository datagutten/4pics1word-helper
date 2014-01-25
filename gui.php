<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>4pics1word</title>
</head>

<body>
<?Php
ini_set('display_errors',1);
error_reporting(E_ALL);
require 'class.php';

$games=array("4pics1word"=>"4 Pics 1 Word","icomania"=>"Icomania","piccombo"=>"Pic Combo");
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
foreach($games as $key=>$game)
{
	echo "    <option value=\"$key\"";
	if($_POST['game']==$key)
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