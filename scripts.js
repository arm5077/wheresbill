const timeline_hours_start = 5;
const timeline_hours_end = 23;
dater = new Date;

function resizeWindow() {
	$(".timeline-container").height(($(window).height() - $("#page-title").height() - $("#header").height() ) *
		.8);
	$("#mapBox").height($(".timeline-container").height());
	// OK, I know I shouldn't have "35" below. But that's the height of the popovers menu bar. I'm tired of trying to time things
	// dynamically but if I ever get my act together, I'm trying to compute $(".popover-title").outerHeight()
	$("#mapBox .popover-content").height($("#mapBox").outerHeight() - 37);
	$("#map").height(($("#map").parent().innerHeight()));
}

function formatForQuery(dateObject) {
  return (dateObject.getFullYear() + '-' + (dateObject.getMonth() < 9 ? '0' : '') + (dateObject.getMonth() + 1) + '-' + (dateObject.getDate() < 10 ? '0' : '') + dateObject.getDate());
}

function formatForDisplay(dateObject) {
	months = ["Jan.", "Feb.", "Mar.", "April", "May", "June", "July", "Aug.",
		"Sept.", "Oct.", "Nov.", "Dec."
	];
  return (months[dateObject.getMonth()] + " " + (dateObject.getDate()) + ", " + dateObject.getFullYear());
}

function getQueryVariable(variable) {
	var query = window.location.search.substring(1);
	//query = query.split("?")[1];
	var vars = query.split("&");
	for (var i=0;i<vars.length;i++) {
		var pair = vars[i].split("=");
		if(pair[0] == variable){return pair[1];}
	}
	return(false);
}

var map;
var layer;
function initializeMap() {
	if(map) {
		map.remove();
	}
	layer = new L.StamenTileLayer("toner");
	map = new L.Map("map", {
		center: new L.LatLng(40.440625, -79.995886),
		zoom: 12,
		minZoom: 11
	});
	map.addLayer(layer);
}

function getSchedule(displayDate) {
	$.getJSON("process.php?operation=getSchedule&date=" + formatForQuery(displayDate), function (schedule) {
		// Clear the timeline
		$(".timeline-entries .list-group .list-group-item").remove();
		$(".timeline-lines .line").remove();
		$(".timeline-lines .dateline").remove();

		//check if schedule has been posted yet
		if (typeof schedule.Result != "undefined") {
			//if not, show GIF
			$("#gif").show();
			$("#map").hide();
		} else {
			$("#gif").hide();
			$("#map").show();
			// Clear the map
			initializeMap();

			//loop through each event on schedule
			$.each(schedule, function (i, event) {
				$(".timeline-entries .list-group").append(
					'<div class="list-group-item" id = "event' + i + '" style = "top:' + (
						event.decimal_start / 24 * 100) + '%; min-height: ' + ((event.decimal_end -
						event.decimal_start) / 24 * 100) +
					'%" > <h4 class="list-group-item-heading">' + event.title +
					'</h4> <p class="list-group-item-text">' + event.start + ' - ' + event.end +
					'</p></div>')
				$("#location").html(event.location);
				
				//load map coordinate
				
				//but first, see if it has a zipcode, which is my way of determining if it's an outside address
				if( !event.location.match(/\b\d{4,5}/) ) event.location += ", Pittsburgh, Pa.";

				$.getJSON(
					"http://maps.googleapis.com/maps/api/geocode/json", {
						sensor: "false",
						address: event.location
					}, function (geocode) {

						//add map marker and click function
						var marker = L.marker([geocode.results[0].geometry.location.lat,
							geocode.results[0].geometry.location.lng
						]).addTo(map);
						marker.on("click", function (e) {
							$(".timeline-container").scrollTo($("#event" + i), 250, {
								offset: -(($(".timeline-container").height() - $("#event" + i).height()) / 2)
							});
							$("#location").html(event.location);
						});
						//zoom to map when event clicked
						$(document).on("click", ("#event" + i), function () {
							map.panTo([geocode.results[0].geometry.location.lat, geocode.results[0].geometry.location.lng])
							map.setZoom(14);
							$("#location").html(event.location);
							$(".list-group-item").removeClass("current");
							$("#event" + i).addClass("current");
						});

						//add waypoint so when viewer scrolls, map changes
						$("#event" + i).waypoint(function (direction) {

							if (direction == "down") {
								map.panTo([geocode.results[0].geometry.location.lat, geocode.results[0].geometry.location.lng])
								map.setZoom(14);
								$("#location").html(event.location);
								$(".list-group-item").removeClass("current");
								$("#event" + i).addClass("current");

							//	$("#event" + i).css("z-index", 999);
							}

						}, {
							context: '.timeline-container',
							offset: (($(".timeline-container").height()) / 2) + "px"
						});

						// waypoint for entering the entry via the bottom
						$("#event" + i).waypoint(function (direction) {
							if (direction == "up") {
								map.panTo([geocode.results[0].geometry.location.lat, geocode.results[
									0].geometry.location.lng])
								map.setZoom(14);
								$("#location").html(event.location);
								$(".list-group-item").removeClass("current");
								$("#event" + i).addClass("current");

							//	$("#event" + i).css("z-index", 999);
							}
						}, {
							context: '.timeline-container',
							offset: (($(".timeline-container").height()) / 2 - $("#event" + i).height()) + "px"
						});
						
						// Bring event bubble to the top when mouse hovers over it
						$(document).on("mouseenter", "#event" + i, function(e) {
							$("#event" + i).css("z-index",999);
						});
						
						$(document).on("mouseleave", "#event" + i, function(e) {
							$("#event" + i).css("z-index",1);
						});

					});


			});
		}


		//resize ticks
		$(".timeline-ticks li").css({
			height: Math.floor($(".timeline-entries").height() / 24) + "px"
		});
		$(".timeline-ticks li").last().height(5);

		//add lines
		for (i = 0; i < 24; i++) {
			$(".timeline-lines").append("<div class='line'></div>");
		}

		//size lines
		$(".timeline-lines .line").height($(".timeline-ticks li").height() - 1);
		$(".timeline-lines .line").last().height(1);

		// add "dateline"
		timer = ((dater.getHours() + dater.getMinutes() / 60) / 23 * 100);
		console.log(dater.getMinutes());
		$(".timeline-lines").append("<div class='dateline' style='top: " + timer + "%'></div>");

		// scroll to time in parameter or, barring that, to the dateline
		// If there's a time parameter, let's have the window scroll to that. Otherwise, we'll use the dateline.
		if( time = getQueryVariable("time") ) 
		{
			timer = ( parseInt(time.substring(0,2)) + parseInt(time.substring(2,4)) / 60 ) / 23 * 100;
		}
		// This addresses a bug in jquery.scrollTo that doesn't allow you to use offsets if you specify a percentage
		// for the target location. Not ideal but it works for now.
		$(".timeline-lines").append("<div class='scrollLine' style='position:absolute; top: " + timer + "%'></div>");
		$(".timeline-container").scrollTo($(".scrollLine"), 1000, {
			offset: -($(".timeline-container").height() / 2)
		});

    // update header date
    $("#display-date").text(formatForDisplay(displayDate));


	});

}

$(document).ready(function () {

	//initialize and resize main page elements
	resizeWindow();

	//initalize map
	initializeMap();

  
	
	
	//pull scheduled events
	if( currentDisplayDate = getQueryVariable("date") )
	{	
		
		getSchedule(currentDisplayDate = new Date(currentDisplayDate+"T00:00:00-0400"));
		$("li.disabled").removeClass("disabled"); //get rid of the disabled "Next day" if we're going to a previous day	
	}
	else getSchedule(currentDisplayDate = new Date());

  
  
	//
	// Events
	//

	$(document).on("click", ".timeline-entries", function (e) {
		$(".timeline-container").scrollTo($(e.target), 500, {
			offset: -(($(".timeline-container").height() - $(e.target).height()) / 2)
		});
	});
	$(document).on("click", "#earlier-date", function(e) {
		e.preventDefault();
		// The earlier arrow never gets its class set to disabled currently, but
		// if it did, it would behave like the later arrow.
		if(!$(this).hasClass('disabled')) {
			currentDisplayDate.setDate(currentDisplayDate.getDate() - 1);
			getSchedule(currentDisplayDate);
			// If we go earlier, then the later arrow should always be enabled.
			$("#later-date").removeClass('disabled');
			// Destroy waypoints on timeline so new ones can be created
			$.waypoints('destroy')
		}
	});
	$(document).on("click", "#later-date", function(e) {
		e.preventDefault();
		if(!$(this).hasClass('disabled')) {
			currentDisplayDate.setDate(currentDisplayDate.getDate() + 1);
			getSchedule(currentDisplayDate);
			// If we just moved to today's date, don't allow going any later
			if(formatForQuery(currentDisplayDate) == formatForQuery(dater)) {
				$("#later-date").addClass('disabled');
			// Destroy waypoints on timeline so new ones can be created
			$.waypoints('destroy')
			}
		}
	});
	$(window).resize(resizeWindow);
	
	$("#otherCredits").click(function(){
		$(this).html("<a href='https://github.com/carols10cents'>carols10cents</a>")
	});	
	

});