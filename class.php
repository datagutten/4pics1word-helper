<?Php
class pics
{
	public $db;
	public $game;
	public $lang='nb';
	public $imagepath;
	public $datapath;
	public $games=array("4pics1word"=>"4 Pics 1 Word"/*,"icomania"=>"Icomania","piccombo"=>"Pic Combo","shadows"=>"Guess the shadow",'megaquiz'=>"Mega quiz"*/);
	public $datafile;
	function __construct($game)
	{
		if(isset($this->games[$game])) //The specified game is valid, use that
			$this->game=$game;
		else
			$this->game='4pics1word'; //The game is not valid, fall back to 4 Pics 1 Word

		$this->datapath='data/'.$this->game;
		$this->imagepath=$this->datapath.'/images';
		if(isset($_GET['lang']))
			$this->lang=$_GET['lang'];
		$this->datafile();
		//Loop through the games array and remove games without data
	}
	public function datafile() //Find correct datafile for current game
	{		
		$datafiles=array("4pics1word"=>"itemData.db");
		
		$this->datafile=$this->datapath.'/'.$datafiles[$this->game];
		if(!file_exists($this->datafile))
			trigger_error("Data file not found: ".$this->datafile,E_USER_ERROR);
		if(substr($this->datafile,-2,2)=='db')
		{
			$this->db=new pdo('sqlite:'.$this->datafile);	
			if($this->db===false)
				trigger_error('Could not open database '.$this->datafile,E_USER_ERROR);
		}
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
		if(!is_numeric($length))
			die("Length must be numeric");
		$st=$this->db->query("SELECT * FROM item WHERE LENGTH(solution)=$length ORDER BY solution");
		if($st===false)
			print_r($this->db->errorInfo());
		$tasks=$st->fetchAll(PDO::FETCH_ASSOC);
		if(empty($tasks))
			die("No words found");

		return $tasks;
	}
	public function possibles($letters,$length)
	{
		if($this->game=='4pics1word' || $this->game=='shadows' || $this->game=='megaquiz') //4 Pics 1 Word store data in an SQLlite database
			$tasks=$this->dbtasks($length); //Get all tasks with the specified length
		elseif($this->game=='icomania' || $this->game=='piccombo')
			$tasks=$this->jsontasks(file_get_contents($this->game."/data/{$this->game}.json"),$length); //Icomania load tasks from json
		else
			die("No tasks for $game");
		$letters_array_base=str_split(strtoupper($letters)); //Make a searchable array of the supplied letters
		foreach ($tasks as $key=>$task)
		{
			$letters_array=$letters_array_base;
			for($pos=0; $pos<$length; $pos++)
			{
				if(($letterkey=array_search(substr(strtoupper($task['solution']),$pos,1),$letters_array))===false) //Check if the word contain one the supplied letters
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
	public function makepicture($task)
	{
		if(!function_exists('imagecreatefromjpeg'))
		{
			echo "GD library not available, can not make images<br />\n";
			return false;
		}
		$font='./arial.ttf';

		$sourcepath=$this->imagepath.'/app';
		if(!file_exists($font))
			trigger_error("The font file $font was not found",E_USER_ERROR);
		if(!file_exists($taskpath=$this->imagepath.'/tasks') && !mkdir($taskpath,0777,true))
			trigger_error("Unable to create a folder for the generated images ($taskpath), check permissions",E_USER_ERROR);
		$taskimagefile=$taskpath.'/'.$task['id'].'.png';

		if(file_exists($taskimagefile))
			return $taskimagefile;
		if($this->game=='piccombo')
			$pics=2;
		elseif($this->game=='4pics1word')
			$pics=4;
		else
			return false;
		for($key=1; $key<=$pics; $key++) //Each picture is $picturesize*$picturesize and there is 10 px between each image
		{
			$imagefile="$sourcepath/_{$task['id']}_$key.jpg";
			if(!file_exists($imagefile))
			{
				if(!$this->downloadpictures($task))
				{
					echo "Image file $imagefile was not found<br>\n";
					return false;
				}
				else
					$imagefile=$this->imagepath."/download/_{$task['id']}_$key.jpg";
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
			{
				trigger_error('Error writing copyright text');
				return false;
			}
		}
		
		imagepng($taskimage,$taskimagefile); //PNG makes larger files, but the background looks cleaner
		return $taskimagefile;
	}
	function downloadpictures($task)
	{
		if(!is_array($task))
			trigger_error("Argument to downloadpictures must be array",E_USER_ERROR);
		if(!file_exists($this->imagepath."/download"))
			mkdir($this->imagepath."/download");	
		if($this->game=='4pics1word')
		{
			for($key=1; $key<=4; $key++)
			{
				$filename="_{$task['id']}_$key.jpg";
				$localfile=$this->imagepath."/download/$filename";
				if(file_exists($localfile))
					continue;
				if(!copy($url="http://4p1w-images.lotum.de/en/$filename",$localfile))
				{
					echo "Downloading of $url failed<br />\n";
					return false;
				}
			}
			return true;
		}
		else
			return false; //Only 4 Pics 1 Word can download pictures
	}
	function image($task)
	{
		if(file_exists($taskimagefile=$this->imagepath."tasks/{$task['id']}.png")) //Check if image exists
			return $taskimagefile;
		elseif($this->game=='4pics1word' || $this->game=='piccombo')
			return $this->makepicture($task);
		elseif($this->game=='icomania' || $this->game=='shadows' || $this->game=="megaquiz")
		{
			if($this->game=='shadows' || $this->game=='megaquiz')
				$rawfile=$this->imagepath.'/tasks_raw/'.$task['picname'].'.png';
			elseif($this->game=='icomania')
				$rawfile=$this->imagepath."tasks_raw/_{$task['id']}.png";
			if(!file_exists($rawfile))
			{
				echo "Could not find image file: $rawfile<br />\n";
				return false;
			}
			if(!function_exists('imagecopyresampled'))
			{
				echo "GD library not available, can not resize images<br />\n";
				return $rawfile;
	   		}
			if(!file_exists($this->imagepath."tasks"))
				mkdir($this->imagepath."tasks");
			$rawimage=imagecreatefrompng($rawfile);
			imagecopyresampled($resized=imagecreatetruecolor(500,500),$rawimage,0,0,0,0,500,500,imagesx($rawimage),imagesy($rawimage)); //Reduce the image size
			imagepng($resized,$taskimagefile);

			return $taskimagefile;
		}
		
		return false; //If the function has not returned before, something is wrong
	}

}