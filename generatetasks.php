<?Php
require 'class.php';
$pics=new pics($argv[1]);
$pics->dbtasks(3); //Call dbtasks to open database
$st=$pics->db->query("SELECT * FROM item");
while($row=$st->fetch(PDO::FETCH_ASSOC))
{
	echo "Creating picture for task {$row['id']}\n";

	if(!$pics->makepicture($row))
		trigger_error("Error creating image for task {$row['id']}",E_USER_WARNING);
}