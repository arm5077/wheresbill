<?PHP

date_default_timezone_set('America/New_York');

$scheduleArray=Array();


$db = mysql_connect("mysql51-010.wc2.dfw1.stabletransit.com", "488441_andrew", "Fish3474") ;
mysql_select_db("488441_andrew", $db);

function decode_entities($text) {
    $text= html_entity_decode($text,ENT_QUOTES,"ISO-8859-1"); #NOTE: UTF-8 does not work!
    $text= preg_replace('/&#(\d+);/me',"chr(\\1)",$text); #decimal notation
    $text= preg_replace('/&#x([a-f0-9]+);/mei',"chr(0x\\1)",$text);  #hex notation
    return $text;
}

function getURL($url)
{
        // create curl resource
        $ch = curl_init();

        // set url
        curl_setopt($ch, CURLOPT_URL, $url);

        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		//do redirects
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		
        // $output contains the output string
        $output = curl_exec($ch);

        // close curl resource to free up system resources
        curl_close($ch);  
	
		return $output;
}

	$content = getURL( "http://pittsburghpa.gov/rss/feed.htm?id=81" );
	$releases = new SimpleXMLElement($content);
	
	
	
	
	for ( $i=0; $i < count($releases->channel->item); $i++ )
	{	
		echo $i;
		
		$item = $releases->channel->item[$i];
	
		if( strpos($item->title, "Public Schedule") != FALSE )
		{
			$schedule = $item->description;
			$published = $item->pubDate[0];
			$export= Array();
			
			
			$scheduleArray = explode( "<p><strong>", $schedule);
			
			for($j=0; $j < count($scheduleArray); $j++)
			{
				$scheduleArray[$j] = strip_tags($scheduleArray[$j]);
				$scheduleArray[$j] = trim(str_replace("&nbsp;", "", $scheduleArray[$j]));
				
				//parse split press release into export array
				$start=0;
				if($scheduleArray[$j] != "") {
					$tempArray = "";
					$tempArray = Array(); //this is where we'll store each event until it goes into the export array
					$lineArray = explode(chr(10), $scheduleArray[$j]);
					
					$tempArray["title"] = decode_entities($lineArray[0]); //the title of the event is always the first line, so let's just do that
					
					
					for($k=1; $k < count($lineArray); $k++)
					{
						// test for time; format if present
						if( stripos($lineArray[$k], "a.m.") != FALSE or stripos($lineArray[$k], "p.m.") or strpos($lineArray[$k], "Time") !== FALSE ) 
						{ 
							//format time string and explode into before and after
							$time = str_replace("Time: ", "", $lineArray[$k]);
							$time = str_replace("Time ", "", $time);
							$time = explode(" - ", $time);
							
							//include unformatted time for debugging purposes
							$tempArray["rawstart"] = $time[0];
							$tempArray["rawend"] = $time[1];
							
							// formatted start date
							$tempArray["start"] = date("H:i:s", strtotime($time[0]));
							
							//check if event has end date... if not, use null
							if ($time[1] != "" ) {
								$tempArray["end"] = date("H:i:s", strtotime($time[1]));
							}
							
						}
						if( stripos($lineArray[$k], "location") === 0) { $tempArray["location"] = str_replace("Location: ", "", $lineArray[$k]); }
					}
					
					if (isset($tempArray["location"]) == FALSE) { $tempArray["location"] = "414 Grant St."; }
					if (isset($tempArray["start"]) == FALSE) { $tempArray["start"] = ""; }
					if (isset($tempArray["end"]) == FALSE) { $tempArray["end"] = ""; }
					
					$export["entries"][]= $tempArray;
				}
			
			}
	
			$export["published"] = date( 'Y-m-d', strtotime((string) $published));
			
			
			
			//time to check if this has already been loaded into the database
			
			$query=mysql_query("SELECT * FROM pedutoSchedule WHERE date = '" . $export["published"] . "'");
			$results=mysql_fetch_assoc($query);
			
			if($results == FALSE)
			{
				foreach($export["entries"] as $entry)
				{
					$query=mysql_query("INSERT INTO pedutoSchedule (title, start, end, location, date) values ('" . mysql_real_escape_string($entry["title"]) . "', '" . $entry["start"] . "', '" . $entry["end"] . "', '" . mysql_real_escape_string($entry["location"]) . "', '" . $export["published"] . "')");
				}
			}
			
			var_dump($export);
		}
	
	}
?>