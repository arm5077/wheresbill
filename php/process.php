<?PHP
include('config.php');
date_default_timezone_set( 'America/New_York' );
$db = mysql_connect($config['mysql_hostname'], $config['mysql_username'], $config['mysql_password']) ;
mysql_select_db($config['mysql_database'], $db);
switch( $_GET[ "operation" ] ) {

	case "getSchedule":
		$requestedDate = date( "Y-m-d" ); // Default to today
		if( $_GET["date"] ) {
			$requestedDate = $_GET["date"];
		}

		$query   = mysql_query( "SELECT * FROM pedutoSchedule WHERE date = '" . $requestedDate . "'" );
		$results = mysql_fetch_assoc( $query );
		if( $results != FALSE ) {
			$export = Array();
			while( $results != FALSE ) {
				if( $results[ "end" ] == "00:00:00" ) {
					$results[ "end" ] = date( "H:i:s", strtotime( $results[ "start" ] ) + 1800 );
				}
				$results[ "decimal_start" ] = intval( date( "G", strtotime( $results[ "start" ] ) ) ) + ( intval( date( "i", strtotime( $results[ "start" ] ) ) ) / 60 );
				$results[ "decimal_end" ]   = intval( date( "G", strtotime( $results[ "end" ] ) ) ) + ( intval( date( "i", strtotime( $results[ "end" ] ) ) ) / 60 );
				$results[ "start" ]         = date( "g:i a", strtotime( $results[ "start" ] ) );
				$results[ "end" ]           = date( "g:i a", strtotime( $results[ "end" ] ) );
				$export[]                   = $results;
				$results                    = mysql_fetch_assoc( $query );
			}
			echo json_encode( $export );
		} else {
			echo json_encode( Array(
				 "Result" => "No schedule today." 
			) );
		}
		break;
	case "getScheduleTest":
		$query   = mysql_query( "SELECT * FROM pedutoSchedule WHERE date = '" . date( "Y-m-d", strtotime( "-1 day" ) ) . "'" );
		$results = mysql_fetch_assoc( $query );
		if( $results != FALSE ) {
			$export = Array();
			while( $results != FALSE ) {
				$results[ "decimal_start" ] = intval( date( "G", strtotime( $results[ "start" ] ) ) ) + ( intval( date( "i", strtotime( $results[ "start" ] ) ) ) / 60 );
				$results[ "decimal_end" ]   = intval( date( "G", strtotime( $results[ "end" ] ) ) ) + ( intval( date( "i", strtotime( $results[ "end" ] ) ) ) / 60 );
				$results[ "start" ]         = date( "g:i a", strtotime( $results[ "start" ] ) );
				$results[ "end" ]           = date( "g:i a", strtotime( $results[ "end" ] ) );
				$export[]                   = $results;
				$results                    = mysql_fetch_assoc( $query );
			}
			echo json_encode( $export );
		} else {
			echo json_encode( Array(
				 "Result" => "No schedule today." 
			) );
		}
		break;
}
?>