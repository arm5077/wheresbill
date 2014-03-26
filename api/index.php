<?PHP
date_default_timezone_set( 'America/New_York' );
include('../php/config.php');

$db = mysql_connect($config['mysql_hostname'], $config['mysql_username'], $config['mysql_password']) ;
mysql_select_db($config['mysql_database'], $db);

$query = mysql_query("SELECT * FROM pedutoSchedule WHERE date like '" . date("Y-m-d",strtotime("today")) . "' AND start BETWEEN ' " . date("H:i:s", strtotime("now")) . " ' and '" . date("H:i:s", strtotime("+30 minutes")) . "'");
$event = mysql_fetch_assoc( $query );

echo json_encode($event);

?>