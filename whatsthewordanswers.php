<?Php
$letters=8;
$data=file_get_contents('http://www.whatsthewordanswers.com/4-pics-1-words-answers-'.$letters.'-letters');
//echo $data;

preg_match('^Page 1 of ([0-9]+)^',$data,$pages);
$pages=$pages[1];

$string='';
$wordlist=array();
$keylist=array();
for ($page=1; $page<=$pages; $page++)
{
$data=file_get_contents('http://www.whatsthewordanswers.com/4-pics-1-words-answers-'.$letters.'-letters/page/'.$page);
preg_match_all('^a href="http://www.whatsthewordanswers.com/(([a-z]+).*)/" rel="bookmark"^',$data,$result);

$wordlist=array_merge($wordlist,$result[2]);
$keylist=array_merge($keylist,$result[1]);
}

/*print_r($wordlist);
print_r($words);*/
$words=$wordlist;
$keys=$keylist;
echo count($words)."\n";
echo count($keys)."\n";

$wordstring=implode("','",$words);
$keystring=implode("','",$keys);
$string='$words['.$letters.']=array(\''.$wordstring."');\r\n";
$string.='$keys['.$letters.']=array(\''.$keystring."');\r\n";

file_put_contents($letters.'word.php',"<?Php\r\n".$string."\r\n?>");