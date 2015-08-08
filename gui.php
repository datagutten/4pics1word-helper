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
input, label {
    display:block;
}	
</style>
<?Php
require 'class.php';
$pics=new pics;
if(isset($_GET['locale']))
	$pics->set_locale($_GET['locale']);

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
<title><?Php echo (isset($pics->game) ? $gamelist[$pics->game] :_("Select game")); ?></title>
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
{
	$pics->error(_("Missing game data"));
}
else
{
?>
	<form id="form1" name="form1" method="post">
		<label for="letters"><?Php echo _('Available letters:');?></label>
    	<input name="letters" type="text" id="letters" />

		<label for="length"><?Php echo _('Number of letters:');?></label>
		<input name="length" type="number" id="length" min="1" size="2" max="99"/>

	<?Php
	if(!isset($pics->game) || count($gamelist)>1)
	{
	?>

    <label for="game"><?Php echo _('Game:');  ?></label>
    <select name="game" id="game">
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

  <?Php
	}
	else
		echo '<input type="hidden" name="game" value="'.$pics->game.'">';
	?>
  <p>
    <input type="submit" name="button" id="button" value="<?Php echo _('Find solution'); ?>" />
  </p>
  <?php
}
?>
  <p><?Php echo sprintf(_('Source code available on %s'),'<a href="https://github.com/datagutten/4pics1word-helper/">github</a>');?></p>
</form>
</body>
</html>