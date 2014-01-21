const timeline_hours_start = 5;
const timeline_hours_end = 23;
dater = new Date;

function resizeWindow() {
	$(".timeline-container").height(($(window).height() - $("#page-title").height()) *
		.9);
	$("#mapBox").height($(".timeline-container").height());
	$("#mapBox .popover-content").height($("#mapBox").outerHeight() - $(
		".popover-title").outerHeight());
	$("#map").height(($("#map").parent().height()));
}

$(document).ready(function () {


	//load current date
	months = ["Jan.", "Feb.", "Mar.", "April", "May", "June", "July", "Aug.",
		"Sept.", "Oct.", "Nov.", "Dec."
	];
	$("#date").html(months[dater.getMonth()] + " " + dater.getDate()); * /
	
	//initialize and resize main page elements
	resizeWindow();

	//initalize map
	var layer = new L.StamenTileLayer("toner");
	var map = new L.Map("map", {
		center: new L.LatLng(40.440625, -79.995886),
		zoom: 12,
		minZoom: 11
	});
	map.addLayer(layer);

	//pull scheduled events
	$.getJSON("process.php?operation=getSchedule", function (schedule) {

		//check if schedule has been posted yet
		if (typeof schedule.Result != "undefined") {
			//if not, show GIF
			$("#mapBox .popover-content").append(
				'<div id ="gif"><div class="text"><h3>No schedule uploaded yet! Guess the \'Dutes is still driving to work!</h3></div>'
			);
		} else {
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
				$.getJSON(
					"http://maps.googleapis.com/maps/api/geocode/json?sensor=false&address=" +
					event.location + ", Pittsburgh, Pa", function (geocode) {

						//add map marker and click function
						var marker = L.marker([geocode.results[0].geometry.location.lat,
							geocode.results[0].geometry.location.lng
						]).addTo(map);
						marker.on("click", function (e) {
							$(".timeline-container").scrollTo($("#event" + i), 250, {
								offset: -(($(".timeline-container").height() - $("#event" + i).height()) /
									2)
							});
							$("#location").html(event.location);
						});
						//zoom to map when event clicked
						$(document).on("click", ("#event" + i), function () {
							map.panTo([geocode.results[0].geometry.location.lat, geocode.results[
								0].geometry.location.lng])
							map.setZoom(14);
							$("#location").html(event.location);
						});

						//add waypoint so when viewer scrolls, map changes
						$("#event" + i).waypoint(function (direction) {

							if (direction == "down") {
								map.panTo([geocode.results[0].geometry.location.lat, geocode.results[
									0].geometry.location.lng])
								map.setZoom(14);
								$("#location").html(event.location);
							}

						}, {
							context: '.timeline-container',
							offset: (($(".timeline-container").height()) / 2) + "px"
						});

						// waypoint for leaving the entry via the bottom
						$("#event" + i).waypoint(function (direction) {
							else {
								map.panTo([geocode.results[0].geometry.location.lat, geocode.results[
									0].geometry.location.lng])
								map.setZoom(14);
								$("#location").html(event.location);
							}


						}, {
							context: '.timeline-container',
							offset: (($(".timeline-container").height()) / 2 - $("#event" + i).height()) +
								"px"
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
		$(".timeline-lines").append("<div class='dateline' style='top: " + ((dater.getHours() +
			dater.getMinutes() / 60) / 24 * 100) + "%'></div>");

		// automatically scroll to dateline on load
		$(".timeline-container").scrollTo($(".dateline"), 1000, {
			offset: -($(".timeline-container").height() / 2)
		});


	});

	//
	// Events
	//

	$(document).on("click", ".timeline-entries", function (e) {
		$(".timeline-container").scrollTo($(e.target), 500, {
			offset: -(($(".timeline-container").height() - $(e.target).height()) / 2)
		});
	});
	$(window).resize(resizeWindow);

});