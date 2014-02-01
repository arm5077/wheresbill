# Where's Bill?

Good question. This script scrapes, stores and displays Pittsburgh Mayor Bill Peduto's public schedule, which is posted every day on the mayoral RSS feed.

## Included files:

- index.html: Display page.
- process.php: Handles AJAX calls from display page, communicates with mySQL server.
- update.php: Scraper that pulls latest schedules from the mayor's RSS feed. Set this up with a daily cronjob (probably around 11 a.m.).
- scripts.js: Javascript code for display page.
- styles.css: CSS styles for display page.
- peduto.jpg, streetsweeper.gif - Fun graphics.

The expected layout of the mySQL table `pedutoSchedule`:

<table>
	<thead>
		<tr>
			<th>Field</th>
			<th>Type</th>
			<th>Purpose</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>title</td>
			<td>text</td>
			<td>Title of event (often all the text that's supplied).</td>
		</tr>
		<tr>
			<td>start</td>
			<td>time</td>
			<td>Start time of event.</td>
		</tr>
		<tr>
			<td>end</td>
			<td>time</td>
			<td>End time of event.</td>
		</tr>
		<tr>
			<td>location</td>
			<td>text</td>
			<td>Location of event.</td>
		</tr>
		<tr>
			<td>date</td>
			<td>date</td>
			<td>Date of event, as per press release.</td>
		</tr>
		<tr>
			<td>published</td>
			<td>date</td>
			<td>Date schedule was published, as per press release publish date.</td>
		</tr>

	</tbody>

</table>

## Setup

To run Where's Bill, you will need to have:

* apache or other web server
* PHP enabled with that web server
* MySQL

1. Run database_setup.sql in MySQL to create a database named `wheresbill` with the schema above.

2. Edit config.php to fill in your connection details.

3. Visit update.php in your browser (wherever you're serving it from) to load any available data into your database.

4. Visit index.html in your browser (wherever you're serving it from) and you should have events loaded from your database (or a gif of Peduto riding a street sweeper if he has no events today)!

To keep your database up to date, you should set up a cronjob to visit update.php every day after the schedule has been posted (probably around 11 a.m.).
