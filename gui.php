<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<style type="text/css">
body {
	margin-left:auto;
	margin-right:auto;
	max-width:500px;
}
.solution {
	max-width:90%;
}
.center {
	margin-left:auto;
	margin-right:auto;
}
.error {
	color:#FF0000;
}
</style>
<?Php
require 'class.php';
$pics=new pics;
$gamelist=$pics->gamelist(); //Get the game list


if(!isset($pics->game)) //If the request URI contains a valid game, use that
{
	$urigame=str_replace('/','',$_SERVER['REQUEST_URI']);
	if(isset($gamelist[$urigame]))
		$pics->selectgame($urigame);
}

if(isset($_POST['game'])) //Game in POST always overrides URI
	$pics->selectgame($_POST['game']);


?>
<title><?Php echo (isset($pics->game) ? $gamelist[$pics->game] :"Select game"); ?></title>
</head>
<body>
<?php

if(isset($_POST['button']))
{
	$gui=true;
	if(is_numeric($_POST['letters']) && !is_numeric($_POST['length'])) //Swapped arguments
	{
		$letters=$_POST['length'];
		$length=$_POST['letters'];
	}
	else
	{
		$letters=$_POST['letters'];
		$length=$_POST['length'];
	}

	$queryletters=strtoupper(preg_replace('/[^a-z]/i','',$letters)); //Remove everything that is not a-z and make the string uppercase

	if($possibles=$pics->possibles($queryletters,$length))
	{
		foreach ($possibles as $key=>$task)
		{
			if($image=$pics->image($task))
				echo "<p><img src=\"$image\" class=\"solution\"/>\n";
			if(isset($task['display']))
				$text=$task['display'];
			else
				$text=$task['solution'];
			echo "<h2>$text</h2></p>\n";
		}
	}
}

if($gamelist===false)
	echo "No game data";
else
{
?>
<form id="form1" name="form1" method="post" action="">
  <p>Available letters:
    <input type="text" name="letters" id="letters" />
  </p>
  <p>Number of letters:
    <input type="text" name="length" id="length" />
  </p>
	<?Php
	if(!isset($pics->game) || count($gamelist)>1)
	{
	?>
  <p>Game: 
    <select name="game" id="select">
<?Php
foreach($gamelist as $key=>$game)
{
	echo "    <option value=\"$key\"";
	if($pics->game==$key)
		echo ' selected="selected"';
	echo ">$game</option>\n";
}
?>
    </select>
  </p>
  <?Php
	}
	else
		echo '<input type="hidden" name="game" value="'.$pics->game.'">';
	?>
  <p>
    <input type="submit" name="button" id="button" value="Submit" />
  </p>
  <?php
}
?>
  <p>Source code available on <a href="https://github.com/datagutten/4pics1word-helper/">github</a>.</p>
</form>
</body>
</html>