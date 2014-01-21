<?PHP
date_default_timezone_set( 'America/New_York' );
$db = mysql_connect( "mysql51-010.wc2.dfw1.stabletransit.com", "488441_andrew", "Fish3474" );
mysql_select_db( "488441_andrew", $db );
switch( $_GET[ "operation" ] ) {
	case "getCurrentEvent":
		$query   = mysql_query( "SELECT * FROM pedutoSchedule WHERE date = '" . date( "Y-m-d" ) . "' AND start < '" . date( "H:i:s" ) . "' AND end > '" . date( "H:i:s" ) . "'" );
		$results = mysql_fetch_assoc( $query );
		if( $results != FALSE ) {
			//format start/end
			$results[ "start" ] = date( "g:i a", strtotime( $results[ "start" ] ) );
			$results[ "end" ]   = date( "g:i a", strtotime( $results[ "end" ] ) );
			echo json_encode( $results );
		} else {
			echo json_encode( Array(
				 "Result" => "No current events." 
			) );
		}
		break;
	case "getCurrentEventTest":
		$query   = mysql_query( "SELECT * FROM pedutoSchedule WHERE AND start < '" . date( "H:i:s" ) . "' AND end > '" . date( "H:i:s" ) . "'" );
		$results = mysql_fetch_assoc( $query );
		if( $results != FALSE ) {
			//format start/end
			$results[ "start" ] = date( "h:i a", strtotime( $results[ "start" ] ) );
			$results[ "end" ]   = date( "h:i a", strtotime( $results[ "end" ] ) );
			echo json_encode( $results );
		} else {
			echo json_encode( Array(
				 "Result" => "No current events." 
			) );
		}
		break;
	case "getPreviousEvent":
		$query   = mysql_query( "SELECT * FROM pedutoSchedule WHERE date = '" . date( "Y-m-d" ) . "' AND start < '" . date( "H:i:s", strtotime( "-0 hours" ) ) . "' ORDER BY start DESC" );
		$results = mysql_fetch_assoc( $query );
		$results = mysql_fetch_assoc( $query );
		if( $results == FALSE )
			echo json_encode( Array(
				 "Result" => "No previous events." 
			) );
		else
			echo json_encode( $results );
		break;
	case "getNextEvent":
		$query   = mysql_query( "SELECT * FROM pedutoSchedule WHERE date = '" . date( "Y-m-d" ) . "' AND start > '" . date( "H:i:s", strtotime( "-0 hours" ) ) . "' ORDER BY start ASC" );
		$results = mysql_fetch_assoc( $query );
		if( $results != FALSE ) {
			//format start/end
			$results[ "start" ] = date( "g:i a", strtotime( $results[ "start" ] ) );
			$results[ "end" ]   = date( "g:i a", strtotime( $results[ "end" ] ) );
			echo json_encode( $results );
		} else {
			echo json_encode( Array(
				 "Result" => "No next events." 
			) );
		}
		break;
	case "getNextEventTest":
		$query   = mysql_query( "SELECT * FROM pedutoSchedule WHERE start > '" . date( "H:i:s", strtotime( "-0 hours" ) ) . "' ORDER BY start ASC" );
		$results = mysql_fetch_assoc( $query );
		if( $results != FALSE ) {
			//format start/end
			$results[ "start" ] = date( "g:i a", strtotime( $results[ "start" ] ) );
			$results[ "end" ]   = date( "g:i a", strtotime( $results[ "end" ] ) );
			echo json_encode( $results );
		} else {
			echo json_encode( Array(
				 "Result" => "No next events." 
			) );
		}
		break;
	case "getSchedule":
		$query   = mysql_query( "SELECT * FROM pedutoSchedule WHERE date = '" . date( "Y-m-d" ) . "'" );
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