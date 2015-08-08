<?Php
class pics
{
	public $db;
	public $game;
	public $lang;
	public $locale_path='./locale';
	public $locale;
	public $dir_data='data'; //Directory for game data and images
	public $dir_gamedata; //Subdir for game data for current game
	public $dir_images; //Subdir for images for current game
	private $games; //Use selectgame() to get game list
	public $datafiles=array("4pics1word"=>"itemData.db",'icomania'=>'icomania_en.json','piccombo'=>'itemdata.json','shadows'=>'levels_en.db');

	public $datafile;

	function __construct()
	{
		$this->games=array(	"4pics1word"=>_("4 Pics 1 Word"),
							"icomania"=>_("Icomania"),
							"piccombo"=>_("Pic Combo"),
							"shadows"=>_("Guess the shadow")/*,
							"megaquiz"=>_("Mega quiz")*/);
	}
	public function error($string)
	{
		if(isset($_GET))
			echo "<div class=\"error\">$string</div>\n";
		else
			trigger_error($string);
	}
	public function set_locale($locale)
	{
		if(!file_exists($file=$this->locale_path."/$locale/LC_MESSAGES/helper.mo"))
		{
			$this->error(sprintf(_("No translation found for locale %s. It should be placed in %s"),$locale,$file));
			return false;
		}
		putenv('LC_ALL='.$locale);
		setlocale(LC_ALL,$locale);
		// Specify location of translation tables
		bindtextdomain("helper", "./locale");
		// Choose domain
		textdomain("helper");

		$this->lang=preg_replace('/([a-z]+)_.+/','$1',$locale); //Get the language from the locale
	}

	public function selectgame($game)
	{
		if(!isset($this->games[$game]))
			return false;
		else
			$this->game=$game;
		$this->dir_gamedata=$this->dir_data.'/'.$this->game.'/gamedata';
		$this->dir_images=$this->dir_data.'/'.$this->game.'/images';
		$this->datafile=$this->dir_gamedata.'/'.$this->datafiles[$this->game];
		
		return $this->games[$game]; //Return the game name
	}
	public function gamelist()
	{
		foreach($this->datafiles as $game=>$datafile) //Check if data files exist
		{
			if(!file_exists($this->dir_data.'/'.$game.'/gamedata/'.$datafile))
				unset($this->games[$game]); //Remove games with no data from game liste
		}
		if(empty($this->games)) //No game data found
			return false;
		elseif(count($this->games)==1)
		{
			$keys=array_keys($this->games);
			$this->game=$keys[0];
			$this->selectgame($this->game);
			return array($this->game=>$this->games[$this->game]);
		}
		else
			return $this->games;
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
	public function opendb()
	{
		if(substr($this->datafile,-2,2)=='db')
		{
			$this->db=new pdo('sqlite:'.$this->datafile);	
			if($this->db===false)
				trigger_error('Could not open database '.$this->datafile,E_USER_ERROR);
		}
	}
	function dbtasks($length) //Get tasks from a database
	{
		if(!isset($this->db))
			$this->opendb();
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
			$tasks=$this->jsontasks(file_get_contents($this->datafile),$length); //Icomania load tasks from json
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

		$sourcepath=$this->dir_images.'/app';
		if(!file_exists($font))
			trigger_error("The font file $font was not found",E_USER_ERROR);
		if(!file_exists($taskpath=$this->dir_images.'/tasks') && !mkdir($taskpath,0777,true))
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
					$imagefile=$this->dir_images."/download/_{$task['id']}_$key.jpg";
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
		if(!file_exists($this->dir_images."/download"))
			mkdir($this->dir_images."/download");	
		if($this->game=='4pics1word')
		{
			for($key=1; $key<=4; $key++)
			{
				$filename="_{$task['id']}_$key.jpg";
				$localfile=$this->dir_images."/download/$filename";
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
		if(file_exists($taskimagefile=$this->dir_images."/tasks/{$task['id']}.png")) //Check if image exists
			return $taskimagefile;
		elseif($this->game=='4pics1word' || $this->game=='piccombo')
			return $this->makepicture($task);
		elseif($this->game=='icomania' || $this->game=='shadows' || $this->game=="megaquiz")
		{
			if($this->game=='shadows' || $this->game=='megaquiz')
				$rawfile=$this->dir_images.'/app/'.$task['picname'].'.png';
			elseif($this->game=='icomania')
				$rawfile=$this->dir_images."/app/_{$task['id']}.png";
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
			if(!file_exists($this->dir_images."/tasks"))
				mkdir($this->dir_images."/tasks");
			$rawimage=imagecreatefrompng($rawfile);
			imagecopyresampled($resized=imagecreatetruecolor(500,500),$rawimage,0,0,0,0,500,500,imagesx($rawimage),imagesy($rawimage)); //Reduce the image size
			imagepng($resized,$taskimagefile);

			return $taskimagefile;
		}
		
		return false; //If the function has not returned before, something is wrong
	}

}