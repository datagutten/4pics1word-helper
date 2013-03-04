<?php
function makepicture($task)
{
	$font='./arial.ttf';
	if(!file_exists($font))
		die("The font file $font was not found<br>\n");
	if(!file_exists('taskimages'))
		if(!mkdir('taskimages'))
			die("Unable to create a folder for the generated images, check permissions<br>\n");
	if(!file_exists('images'))
		die("Image folder was not found<br>\n");
	$taskimage=imagecreatetruecolor(610,610);
	
	$text=imagecolorallocate($taskimage,255,255,255); //Make the text color
	$background=imagecolorallocate($taskimage,85,92,99); //Make the background color (same as the frame in the app)
	imagefill($taskimage,0,0,$background); //Fill the image with background color
	
	$posx=array(10,10,310,310); //positions from top
	$posy=array(10,310,310,10); //positions trom left
	foreach($task['copyrights'] as $picture_key=>$picture) //Each pictures is 290x290 and there is 10 px between each image
	{
		$filekey=$picture_key+1;
		$imagefile="images/_{$task['id']}_$filekey.jpg";
		if(!file_exists($imagefile))
			die("Image file $imagefile was not found<br>\n");
		$im[$picture_key]=imagecreatefromjpeg($imagefile);
		imagecopy($taskimage,$im[$picture_key],$posx[$picture_key],$posy[$picture_key],0,0,290,290);
		$textypos=290+$posy[$picture_key]+7+1; //The Y position is the picture size+the image Y position+the font size+1
		imagettftext($taskimage,7,0,$posx[$picture_key],$textypos,$text,$font,$task['copyrights'][$picture_key]); //Write the copyright text on the image
	}
	
	imagepng($taskimage,"taskimages/{$task['id']}.png"); //PNG makes larger files, but the background looks cleaner
}
?>