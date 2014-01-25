<?Php
class pics
{
	public $db;
	public $game;
	public $imagepath;
	public $datapath;
	function __construct($game)
	{
		$this->game=$game;
		if($game=='4pics1word')
		{
			$this->opendb($this->game."/data/itemData.db");
		}
		elseif($game!='icomania' && $game!='piccombo')
			die("Invalid game: $game");
		$this->imagepath=$this->game."/images/";
		$this->datapath=$this->game."/data/";
	}
	public function opendb($dbfile)
	{
		$this->db=new pdo('sqlite:'.$dbfile);	
	}
	public function jsontasks($json,$length) //Get tasks from a json file
	{
		$alltasks=json_decode($json,true);
		foreach($alltasks as $task)
		{
			$task['solution']=preg_replace('/[^a-z]/i','',$task['solution']); //Remove symbols from the solution
			if(isset($task['copyrights']) && is_array($task['copyrights']))
			{
				foreach($task['copyrights'] as $key=>$copyright)
				{
					$key++;
					$task['copyright'.$key]=$copyright;	
				}
			}
			
			if(strlen($task['solution'])==$length) //Check if the solution has the right length
				$outtasks[]=$task;
		}
		if(!isset($outtasks)) //No tasks found
			return false;
		else
			return $outtasks;
	}
	function dbtasks($length) //Get tasks from a database
	{
		if(!isset($this->db))
		{
			echo "No database connection\n";
			return false;	
		}
		if(!is_numeric($length))
			die("Length must be numeric");
		$st=$this->db->query("SELECT * FROM item WHERE LENGTH(solution)=$length ORDER BY solution");
		$tasks=$st->fetchAll(PDO::FETCH_ASSOC);
		if(empty($tasks))
			die("No words found");

		return $tasks;
	}
	public function possibles($letters,$length)
	{
		if($this->game=='4pics1word')
			$tasks=$this->dbtasks($length); //Get all tasks with the specified length
		elseif($this->game=='icomania' || $this->game=='piccombo')
			$tasks=$this->jsontasks(file_get_contents($this->game."/data/{$this->game}.json"),$length); //Icomania load tasks from json
		$letters_array_base=str_split(strtoupper($letters)); //Make a searchable array of the supplied letters

		foreach ($tasks as $key=>$task)
		{
			$letters_array=$letters_array_base;
			for($pos=0; $pos<$length; $pos++)
			{
				if(($letterkey=array_search(substr($task['solution'],$pos,1),$letters_array))===false) //Check if the word contain one the supplied letters
					continue 2; //The word does not contain one of the supplied letters, try next word
				unset($letters_array[$letterkey]);
			}
	
			$possibles[$key]=$task; //All the letters in the word are available
		}
		
		if(!isset($possibles))
		{
			echo "No $length letter word containing the letters \"$letters\" was found\n";
			return false;
		}
		else
			return $possibles;
	}
	public function makepicture($task,$sourcepath)
	{
		if(!function_exists('imagecreatefromjpeg'))
		{
			echo "GD library not available, can not make images<br />\n";
			return false;
		}
		$font='./arial.ttf';
		if(!file_exists($font))
			die("The font file $font was not found<br>\n");
		if(!file_exists($taskpath=$this->imagepath.'tasks/'))
			if(!mkdir($taskpath))
				die("Unable to create a folder for the generated images, check permissions<br>\n");
		if(!file_exists($sourcepath))
			die("Image folder $sourcepath was not found<br>\n");
		$taskimagefile=$taskpath.$task['id'].'.png';
		/*if(file_exists($taskimagefile="taskimages/{$task['id']}.png"))
			return $taskimagefile;*/
		if($this->game=='piccombo')
			$pics=2;
		elseif($this->game=='4pics1word')
			$pics=4;
		else
			return false;
		for($key=1; $key<=$pics; $key++) //Each pictures is $picturesizex$picturesize and there is 10 px between each image
		{
			$imagefile="$sourcepath/_{$task['id']}_$key.jpg";
			if(!file_exists($imagefile))
			{
				echo "Image file $imagefile was not found<br>\n";
				return false;
			}
			$im[$key]=imagecreatefromjpeg($imagefile);
			if($key==1)
			{
				$picturesize=imagesy($im[$key]);
				$tasksize=$picturesize+$picturesize+30; //The width and height of the task picture is the size of two pictures plus three 10px borders
				if($pics==2)
					$taskimage=imagecreatetruecolor($tasksize,$picturesize+20);
				else
					$taskimage=imagecreatetruecolor($tasksize,$tasksize);
		
				$text=imagecolorallocate($taskimage,255,255,255); //Make the text color
				$background=imagecolorallocate($taskimage,85,92,99); //Make the background color (same as the frame in the app)
				imagefill($taskimage,0,0,$background); //Fill the image with background color
				
				$posy=array(NULL,10,10,$picturesize+20,$picturesize+20); //positions from top
				$posx=array(NULL,10,$picturesize+20,$picturesize+20,10); //positions trom left
			}
			
			imagecopy($taskimage,$im[$key],$posx[$key],$posy[$key],0,0,$picturesize,$picturesize);
			$textypos=$picturesize+$posy[$key]+7+1; //The Y position is the picture size+the image Y position+the font size+1
			if(!imagettftext($taskimage,7,0,$posx[$key],$textypos,$text,$font,$task['copyright'.$key])) //Write the copyright text on the image
				die('Error writing copyright text');
		}
		
		imagepng($taskimage,$taskimagefile); //PNG makes larger files, but the background looks cleaner
		return $taskimagefile;
	}
	function downloadpictures($task)
	{
		if($this->game=='4pics1word')
		{
			for($key=1; $key<=4; $key++)
			{
				$filename="_{$task['id']}_$key.jpg";
				copy("http://4p1w-images.lotum.de/en/$filename",$this->imagepath."download/$filename");
			}
		}
		else
			return false; //Only 4 Pics 1 Word can download pictures
	}
	function image($task)
	{
		if(file_exists($taskimagefile=$this->imagepath."tasks/{$task['id']}.png"))
			return $taskimagefile;
		if($this->game=='4pics1word')
		{
			if(file_exists($this->imagepath."app/_{$task['id']}_1.jpg"))
				return $this->makepicture($task,$this->imagepath.'app');
			elseif(file_exists($this->imagepath."download/_{$task['id']}_1.jpg") || $this->downloadpictures($task)) //Try to download images
				return $this->makepicture($task,$this->imagepath.'download');
			else
				return false;
		}
		elseif($this->game=='icomania')
		{
			if(!file_exists($rawfile=$this->imagepath."tasks_raw/_{$task['id']}.png"))
			{
				echo "Could not find image file: $rawfile";
				return false;	
			}
			if(!function_exists('imagecopyresampled'))
			{
				echo "GD library not available, can not resize images<br />\n";
				return $rawfile;
	   		}
			$rawimage=imagecreatefrompng($rawfile);
			imagecopyresampled($resized=imagecreatetruecolor(500,500),$rawimage,0,0,0,0,500,500,imagesx($rawimage),imagesy($rawimage)); //Reduce the image size
			imagepng($resized,$taskimagefile);

			return $taskimagefile;
		}
		elseif($this->game=='piccombo')
			return $this->makepicture($task,$this->imagepath.'raw');
		
		return false; //If the function has not returned before, something is wrong
	}

}