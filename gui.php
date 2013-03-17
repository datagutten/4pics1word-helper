<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>4pics1word</title>
</head>

<body>
<?Php
if(isset($_POST['button']))
{
	$gui=true;
	$queryletters=strtoupper(preg_replace('/[^a-z]/i','',$_POST['letters'])); //Remove everything that is not a-z and make the string uppercase
	$letters=$_POST['number'];
	require '4pics1word.php';
	require 'picturemaker.php';
	foreach ($possibles as $key=>$possible)
	{
		//echo "<p><img src=\"http://www.whatsthewordanswers.com/images/fourpics/{$key}.jpg\" />\n";
		$task=$tasks[array_search(strtoupper($possible),$solutions)];
		if(!file_exists($image="taskimages/{$task['id']}.png"))
			makepicture($task);
		echo "<p><img src=\"$image\" />\n";
		
		echo "<h2>$possible</h2></p>\n";
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
  <p>
    <input type="submit" name="button" id="button" value="Submit" />
  </p>
  <p>Source code available on <a href="https://github.com/datagutten/4pics1word-helper/">github</a>.</p>
</form>
</body>
</html>