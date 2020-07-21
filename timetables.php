<?php


# Class to create an online timetable system
require_once ('frontControllerApplication.php');
class timetables extends frontControllerApplication
{
	# Function to assign defaults additional to the general application defaults
	public function defaults ()
	{
		# Specify available arguments as defaults or as NULL (to represent a required argument)
		$defaults = array (
			'div' => strtolower (__CLASS__),
			'database' => 'timetables',
			'username' => 'timetables',
			'password' => NULL,
			'table' => false,
			'databaseStrictWhere' => true,
			'administrators' => true,
			'termLabels' => array (),
			'usersExternalDatabase' => false,	// Must contain fields username,title,forename,surname
			'dayHeightPx' => 112,
			'overlapPx' => 44,
			'jQuery' => true,
			'usernameRegexp' => '^([a-z]{2,6})([1-9])([0-9]{0,4})$',	// Regexp (must be compatible with both MySQL and with PHP's preg_match function) of a real username
			'daterangeCookieName' => 'daterange',
			'daterangeCookieSeparator' => ';',
			'apiUsername'			=> false,		// Optional API access
			'itemCaseSensitive' => true,
			'tabUlClass' => 'tabsflat',
		);
		
		# Return the defaults
		return $defaults;
	}
	
	
	# Function to assign supported actions
	public function actions ()
	{
		# Define available actions
		$actions = array (
			'home' => array (
				'description' => 'Browse the timetable',
				'url' => '',
				'tab' => 'Home',
				'icon' => 'date',
			),
			'browse' => array (
				'usetab' => 'Home',
				'description' => 'Browse the timetable',
				'url' => '',
			),
			/*
			#!# Disabled until ready
			'my' => array (
				'description' => 'View my timetable',
				'url' => 'my/',
				'tab' => 'My timetable',
				'icon' => 'asterisk_orange',
				'authentication' => true,
			),
			*/
			'bookings' => array (
				'description' => 'Bookings',
				'url' => 'bookings/add.html',
				'tab' => 'Bookings',
				'icon' => 'add',
				'privilege' => 'userIsEditor',
			),
			'activities' => array (
				'description' => 'Areas of activity',
				'heading' => false,
				'url' => 'activities/',
				'tab' => 'Areas of activity',
				'icon' => 'sitemap_color',
				'crudViewAsTimetable' => true,
				//'privilege' - this is done in the function logic as it must be limited only to editing
				'descriptionSingular' => 'area of activity',
				'table' => 'areaOfActivity',
			),
			'rooms' => array (
				'description' => 'Rooms',
				'heading' => false,
				'url' => 'rooms/',
				'tab' => 'Rooms',
				'icon' => 'door',
				'crudViewAsTimetable' => true,
				'descriptionSingular' => 'room',
				//'privilege' - this is done in the function logic as it must be limited only to editing
			),
			'people' => array (
				'description' => 'People',
				'heading' => false,
				'url' => 'people/',
				'tab' => 'People',
				'icon' => 'user',
				'crudViewAsTimetable' => true,
				'descriptionSingular' => 'person',
				//'privilege' - this is done in the function logic as it must be limited only to editing
			),
			'editors' => array (
				'description' => 'Editors',
				'url' => 'editors/',
				'tab' => 'Editors',
				'icon' => 'shield',
				// 'administrator' => true, - actually we should allow Editors to add new Editors
			),
			'more' => array (
				'description' => 'More&hellip;',
				'url' => 'more.html',
				'tab' => 'More&hellip;',
				'icon' => 'application_double',
				'privilege' => 'userIsEditor',
			),
			'buildings' => array (
				'description' => 'Buildings',
				'url' => 'buildings/',
				'parent' => 'more',
				'subtab' => 'Buildings',
				'icon' => 'images',
				'privilege' => 'userIsEditor',
			),
			'eventtypes' => array (
				'description' => 'Event types',
				'url' => 'eventtypes/',
				'parent' => 'more',
				'subtab' => 'Event types',
				'icon' => 'shape_move_back',
				'privilege' => 'userIsEditor',
				'table' => 'eventTypes',
			),
			'terms' => array (
				'description' => 'Term dates',
				'url' => 'terms/',
				'parent' => 'more',
				'subtab' => 'Term dates',
				'icon' => 'text_smallcaps',
				'privilege' => 'userIsEditor',
			),
			'specialdates' => array (
				'description' => 'Special dates',
				'url' => 'specialdates/',
				'parent' => 'more',
				'subtab' => 'Special dates',
				'icon' => 'textfield_key',
				'privilege' => 'userIsEditor',
				'table' => 'specialDates',
			),
			'today' => array (
				'description' => 'Lobby screen page',
				'url' => 'today/',
				'parent' => 'more',
				'subtab' => 'Lobby screen page',
				'icon' => 'picture_empty',
				'export' => true,
			),
			'maintenance' => array (
				'description' => 'Data maintenance',
				'url' => 'maintenance.html',
				'administrator' => true,
				'parent' => 'admin',
				'subtab' => 'Data maintenance',
			),
			'consolidate' => array (
				'description' => 'Consolidate user entries',
				'url' => 'people/consolidate/',
				'parent' => 'admin',
				'usetab' => 'people',
				'subtab' => 'Consolidate user entries',
				'privilege' => 'userIsEditor',
			),
			'clone' => array (
				'description' => 'Clone bookings',
				'url' => 'clone.html',
				'parent' => 'admin',
				'subtab' => 'Clone bookings for period',
				'privilege' => 'userIsEditor',
			),
		);
		
		# Return the actions
		return $actions;
	}
	
	
	# Database structure definition
	public function databaseStructure ()
	{
		return "
			
			-- System administrators
			CREATE TABLE `administrators` (
			  `username__JOIN__people__people__reserved` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Username',
			  `active` enum('','Yes','No') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Yes' COMMENT 'Currently active?',
			  `privilege` enum('Administrator','Restricted administrator') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Administrator' COMMENT 'Administrator level',
			  PRIMARY KEY (`username__JOIN__people__people__reserved`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='System administrators';
			
			-- Area of activity
			CREATE TABLE `areaOfActivity` (
			  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Automatic key',
			  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Description',
			  `parentId` int(11) NOT NULL COMMENT 'Parent area of activity',
			  `shortname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Optional short name',
			  `moniker` varchar(40) COLLATE utf8_unicode_ci NOT NULL COMMENT 'URL component (must be unique)',
			  `createdOn` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Automatic timestamp',
			  `people` text COLLATE utf8_unicode_ci COMMENT 'People always associated with this activity (usernames, one per line)',
			  `website` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Website',
			  `hideFromNew` int(1) DEFAULT NULL COMMENT 'Whether to hide this for new event creation',
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `moniker` (`moniker`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Area of activity';
			
			-- Bookings
			CREATE TABLE `bookings` (
			  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Booking no.',
			  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Event title',
			  `areaOfActivityId` int(11) NOT NULL COMMENT 'Applies to everyone in',
			  `eventTypeId` int(11) NOT NULL COMMENT 'Event type',
			  `bookedForUserid` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Who the booking is for',
			  `roomId` int(11) NOT NULL COMMENT 'Room / location',
			  `date` date NOT NULL COMMENT 'Date',
			  `startTime` time NOT NULL COMMENT 'Start time',
			  `untilTime` time NOT NULL COMMENT 'Finishing time (until)',
			  `series` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Series ID',
			  `url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Web link, if any',
			  `notes` text COLLATE utf8_unicode_ci COMMENT 'Miscellaneous notes',
			  `draft` int(1) DEFAULT NULL COMMENT 'Draft booking (hidden for now)?',
			  `requestedBy` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Requested booking by',
			  `hideFromDisplayBoard` int(1) DEFAULT NULL COMMENT 'Hide from display board listing?',
			  `bookedByUserid` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Person making the booking',
			  `updatedByUserid` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Person updating the booking',
			  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Automatic timestamp (created at)',
			  PRIMARY KEY (`id`),
			  KEY `date` (`date`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Bookings';
			
			-- Buildings
			CREATE TABLE `buildings` (
			  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Automatic key',
			  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Name of the building',
			  `moniker` varchar(40) COLLATE utf8_unicode_ci NOT NULL COMMENT 'URL component (must be unique)',
			  `isInternal` int(1) DEFAULT NULL COMMENT 'Is this building internal?',
			  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Automatic timestamp',
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `moniker` (`moniker`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Buildings';
			
			-- Editors
			CREATE TABLE `editors` (
			  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Automatic key',
			  `userid` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'User ID',
			  `reviewer` int(1) DEFAULT NULL COMMENT 'Reviewer?',
			  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Automatic timestamp',
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `userId` (`userid`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Editors';
			
			-- Event types
			CREATE TABLE `eventTypes` (
			  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Automatic key',
			  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Description of event type',
			  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Event types';
			
			-- People
			CREATE TABLE `people` (
			  `id` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Username',
			  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Title',
			  `forename` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Forename',
			  `surname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Surname',
			  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Name (automatically generated)',
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='People';
			
			-- Rooms
			CREATE TABLE `rooms` (
			  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Automatic key',
			  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Name of room',
			  `buildingId` int(11) DEFAULT NULL COMMENT 'Building',
			  `moniker` varchar(40) COLLATE utf8_unicode_ci NOT NULL COMMENT 'URL component (must be unique)',
			  `note` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Note to other Editors',
			  `longitude` float(11,6) DEFAULT NULL COMMENT 'Map longitude',
			  `latitude` float(10,6) DEFAULT NULL COMMENT 'Map latitude',
			  `universityMapUrl` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'University Map link',
			  `suppressedFromListingsByDefault` int(1) DEFAULT NULL COMMENT 'Suppress from listings?',
			  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Automatic timestamp',
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `moniker` (`moniker`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Rooms';
			
			-- Seeded dates (populated below)
			CREATE TABLE `seededDates` (
			  `date` date NOT NULL,
			  PRIMARY KEY (`date`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Seeded dates';
			
			-- Settings
			CREATE TABLE `settings` (
			  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Automatic key (ignored)',
			  `workingDayStartHour` int(2) NOT NULL COMMENT 'Working day start hour (0-23)',
			  `workingDayUntilHour` int(2) NOT NULL COMMENT 'Working day until hour (0-23)',
			  `startingDayNaturalWeek` int(1) NOT NULL DEFAULT '1' COMMENT 'The starting day of a natural week',
			  `showWeekends` int(1) DEFAULT NULL COMMENT 'Whether to show weekends',
			  `minimumWeeks` int(2) NOT NULL DEFAULT '4' COMMENT 'Minimum weeks to show in certain views',
			  `maximumWeeks` int(2) NOT NULL DEFAULT '8' COMMENT 'Maximum weeks to show in certain views',
			  `startingDayCustomWeek` int(1) DEFAULT NULL COMMENT 'The starting day of a custom week',
			  `anyUsernameAccess` enum('Yes','No') COLLATE utf8_unicode_ci NOT NULL COMMENT 'Whether access is available to anyone with a username (rather than just those in a list of people)',
			  `startingMonthCustomYear` int(2) NOT NULL COMMENT 'Starting month number of custom year (e.g. 10 = October)',
			  `customYearLabel` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Label for custom year (or blank to disable)',
			  `flaggedEventsNumber` int(11) NOT NULL COMMENT 'Number of events in the Flagged events list',
			  `termLabels` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Term labels',
			  `weeksInTerm` int(2) DEFAULT NULL COMMENT 'The number of weeks in a term',
			  `weeksInTermFirstNumber` int(1) DEFAULT NULL COMMENT 'The numbering of the first week in term (e.g. 1 or 0)',
			  `termDatesUrl` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Web link URL showing term dates reference',
			  `skipPreviousDays` int(1) DEFAULT NULL COMMENT 'Whether to enable skipping of past days in the table views',
			  `yearsAheadBookable` int(2) NOT NULL COMMENT 'How many years ahead are bookable',
			  `yearsBehindBookable` int(2) DEFAULT NULL COMMENT 'How many years behind are bookable',
			  `defaultBookingLengthHours` int(1) NOT NULL DEFAULT '1' COMMENT 'Default length in hours of a booking when following a link from the grid view',
			  `maxVisibleListingsPerTimeslotWhenOverlapping` int(2) NOT NULL DEFAULT '4' COMMENT 'Maximum number of visible listings per timeslot (caused by overlapping)',
			  `filteringControlWeeksAheadMax` int(2) NOT NULL DEFAULT '10' COMMENT 'For the filtering box, the maximum number of weeks ahead selectable',
			  `filteringControlWeeksAheadDefault` int(2) NOT NULL DEFAULT '8' COMMENT 'For the filtering box, the default number of weeks ahead selectable',
			  `paginationRecordsPerPage` int(3) NOT NULL DEFAULT '25' COMMENT 'Pagination records per page',
			  `institutionDescription` VARCHAR(255) NOT NULL DEFAULT 'Department' COMMENT 'Institution description',
			  `usersAutocomplete` VARCHAR(255) NULL COMMENT 'Users autocomplete URL',
			  `usersExternalUrl` VARCHAR(255) NULL COMMENT 'Users database UI external URL',
			  `calendarName` VARCHAR(255) NOT NULL DEFAULT 'calendar' COMMENT 'iCal calendar name',
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Settings';
			
			-- Special dates, e.g. bank holidays
			CREATE TABLE `specialDates` (
			  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Automatic key',
			  `date` date NOT NULL COMMENT 'Date',
			  `type` enum('','Bank holiday','Closure','Other') COLLATE utf8_unicode_ci NOT NULL COMMENT 'Type',
			  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Description',
			  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Automatic timestamp',
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `date` (`date`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Special dates, e.g. bank holidays';
			
			-- Term dates
			CREATE TABLE `terms` (
			  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Automatic key',
			  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Name',
			  `startYear` year(4) NOT NULL COMMENT 'Start year in year range',
			  `endYear` year(4) NOT NULL COMMENT 'End year in year range',
			  `termLabel` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Term label',
			  `startDate` date NOT NULL COMMENT 'The date the term starts on',
			  `untilDate` date NOT NULL COMMENT 'The last day of term',
			  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Automatic timestamp',
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Term dates';
			
			-- User profiles
			CREATE TABLE `users` (
			  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Username',
			  `startDate` date DEFAULT NULL COMMENT 'Start date for main listing',
			  `untilDate` date DEFAULT NULL COMMENT 'Until date for main listing',
			  `weeksAhead` int(2) NOT NULL COMMENT 'Weeks ahead default',
			  `updatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='User profiles';
			
			-- Populate the seeded dates table with dates from 1970 to 2038; see https://www.artfulsoftware.com/infotree/qrytip.php?id=95 and https://web.archive.org/web/20110309002522/creative-territory.net/post/view/id/31/
			CREATE TABLE tempDigits (i INT NOT NULL PRIMARY KEY);
			INSERT INTO tempDigits VALUES (0),(1),(2),(3),(4),(5),(6),(7),(8),(9);
			INSERT INTO seededDates (
				SELECT
					'1970-01-01' + INTERVAL (r.i*10000 + s.i*1000 + t.i*100 + u.i*10 + v.i*1) DAY AS `date`
					FROM tempDigits AS r
					JOIN tempDigits AS s
					JOIN tempDigits AS t
					JOIN tempDigits AS u
					JOIN tempDigits AS v
					WHERE (r.i*10000 + s.i*1000 + t.i*100 + u.i*10 + v.i*1) < 100000
					ORDER BY `date`
				);
			DELETE FROM seededDates WHERE `date` > '2038-01-19';
			DROP TABLE tempDigits;
		";
	}
	
	
	# Additional processing, run before actions is processed
	public function mainPreActions ()
	{
		# Determine if this user is an editor
		$this->userIsEditor = $this->userIsEditor ();
		
		# Set the status label to editor if required
		if ($this->userIsEditor) {
			$this->setUserStatus ('Editor');
		}
		
	}
	
	
	# Additional processing
	public function main ()
	{
		# Define day and month names
		$this->days   = array (1 => 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
		$this->months = array (1 => 'january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december');
		
		# Determine hidden fields
		$this->hideFields = array ('createdAt', 'createdOn', 'updatedAt', 'updatedOn', );
		
		# Define fieldnames containing user names
		$this->userFields = array ('userid', 'bookedForUserid', 'bookedByUserid', 'updatedByUserid', 'people', );
		
		# Define the default date state
		$this->defaultDateState = array (
			'startDate' => date ('Y-m-d'),
			'weeksAhead' => min ($this->settings['filteringControlWeeksAheadDefault'], $this->settings['filteringControlWeeksAheadMax']),
		);
		
		# Get the user profile
		$this->userProfile = $this->getUserProfile ();
		
		# Determine if an export format is being requested
		$exportFormats = array (
			'timetable.ics'	=> 'ics',
			'export.html'	=> true,
			'timetable.csv'	=> 'csv',
		);
		$this->export = (isSet ($_GET['export']) && strlen ($_GET['export']) && isSet ($exportFormats[$_GET['export']]) ? $exportFormats[$_GET['export']] : false);
		
	}
	
	
	# Settings
	public function settings ($dataBindingSettingsOverrides = array ())
	{
		#!# Need to be able to convert the field skipPreviousDays to a single checkbox as per int1ToCheckbox, but not other fields
		
		# Define overrides
		$dataBindingSettingsOverrides = array (
			'attributes' => array (
				#!# Currently no support for startingDayNaturalWeek to be anything other than 1 - there are various hard-coded instances of Monday being used
				'startingDayNaturalWeek' => array ('type' => 'select', 'values' => $this->dayNamesFormatted (false, true), 'default' => 1, 'editable' => false, ),
				'startingDayCustomWeek'  => array ('type' => 'select', 'values' => $this->dayNamesFormatted (false, true), ),
			),
		);
		
		# Run the main settings system with the overriden attributes
		return parent::settings ($dataBindingSettingsOverrides);
	}
	
	
	# Home page
	public function home ()
	{
		# Start the HTML
		$html  = '';
		$html .= $this->minisearch ('bookings', 'bookings');
		$html .= "\n<p>Welcome to the timetable system.</p>";
		//$html .= "\n<p>You can view <a class=\"actions\" href=\"{$this->baseUrl}/my/\">" . '<img src="/images/icons/asterisk_orange.png" alt="" class="icon" />' . " My timetable</a> to view your timetable or browse through other listings:</p>";
		
		# Start a list of expandable panels
		$panels = array ();
		
		# Add custom year links
		#!# Should just show the current term weeks
		if ($this->settings['customYearLabel']) {
			$panels['customyears']  = "<h3>View timetable for " . htmlspecialchars ($this->settings['customYearLabel']) . '&hellip;</h3>';
			$panels['customyears'] .= $this->yearLinks (true);
		}
		
		# Add year links
		$panels['years']  = "<h3>View timetable for year&hellip;</h3>";
		$panels['years'] .= $this->yearLinks ();
		
		# Add rooms
		$panels['rooms']  = "<h3>View timetable for room&hellip;</h3>";
		if ($this->userIsEditor) {$panels['rooms'] .= "<p class=\"editorlink\"><em>As an Editor, you can <a href=\"{$this->baseUrl}/rooms/\">edit this list</a>.</em></p>";}
		$panels['rooms'] .= $this->roomsLinks ();
		
		# Add people links
		$panels['people']  = "<h3>View timetable for person&hellip;</h3>";
		$panels['people'] .= $this->peopleLinks ();
		
		# Add activities links
		$panels['activities']  = "<h3>View timetable for activity&hellip;</h3>";
		if ($this->userIsEditor) {$panels['activities'] .= "<p class=\"editorlink\"><em>As an Editor, you can <a href=\"{$this->baseUrl}/activities/\">edit this list</a>.</em></p>";}
		$panels['activities'] .= $this->activitiesLinks (false, false);
		
		# Convert the panels to an expandable listing
		#!# Need to add state memory to this
		require_once ('jquery.php');
		$jQuery = new jQuery (/* $this->databaseConnection, "{$this->baseUrl}/data.html", $_SERVER['REMOTE_USER'] */ false, false, false, true);
		$jQuery->expandable ($panels /*, $expandState, $saveState */);
		$html .= $jQuery->getHtml ();
		
		# Get the HTML from the browsing listing
		$html .= $this->browsingListing (array (), false, 'home');
		
		# Show the HTML
		echo $html;
	}
	
	
	# Helper function to get the list of unique usernames from the bookings
	private function getUniqueUsernames ()
	{
		# Get all the people strings
		$query = "SELECT
			DISTINCT bookedForUserid
			FROM bookings
			WHERE bookedForUserid IS NOT NULL AND bookedForUserid != ''
		;";
		$data = $this->databaseConnection->getPairs ($query);
		
		#!# This also needs to look at areaOfActivity.people
		
		# Convert each string to a uniqued list of usernames
		$usernames = application::splitCombinedTokenList ($data);
		
		# Return the usernames
		return $usernames;
	}
	
	
	# Function to get the user profile
	private function getUserProfile ()
	{
		# By default, use the default date state
		$data = $this->defaultDateState;
		
		# If there is a cookie present, use that
		if ($cookieProfile = $this->getCookieProfile ()) {
			$data = $cookieProfile;
		}
		
		# If there is a profile in the database, use that in preference
		if ($databaseProfile = $this->getDatabaseProfile ()) {
			$data = $databaseProfile;
		}
		
		# However, if both a database and cookie profile are supplied, use the more recent (i.e. if database is more recent, undo the overwriting of the cookie profile), and copy (transfer) the cookie values into the database
		if ($databaseProfile && $cookieProfile) {
			if ($cookieProfile['updatedAt'] > $databaseProfile['updatedAt']) {
				$data = $cookieProfile;
				$this->saveImplicitViewDates ($data['startDate'], $data['weeksAhead']);
			}
		}
		
		# Remove the updatedAt timestamp
		unset ($data['updatedAt']);
		
		# Add in the untilDate, which is always calculated
		$startTimestamp = strtotime ($data['startDate'] . ' 01:01:00');
		#!# Should be one day, but there is an orphaned week bug (but the actual bookings do not get shown on the orphaned week layout)
		$days = ($data['weeksAhead'] * 7) - 2;	// Minus one day to avoid an orphaned hour causing a whole week to display
		$data['untilDate'] = date ('Y-m-d', $startTimestamp + (60*60*24 * $days));
		
		# Return the default
		return $data;
	}
	
	
	# Function get a database profile
	private function getDatabaseProfile ()
	{
		# End if none
		if (!$data = $this->databaseConnection->selectOne ($this->settings['database'], 'users', array ('id' => $this->user), array ('startDate', 'weeksAhead', 'UNIX_TIMESTAMP(updatedAt) AS updatedAt'))) {return false;}
		
		# Validate the profile
		if (!$this->validProfile ($data)) {
			$this->databaseConnection->delete ($this->settings['database'], 'users', array ('id' => $this->user));	// Delete the invalid entry to keep the database clean
			return false;
		}
		
		# Return the data
		return $data;
	}
	
	
	# Function to get a cookie profile
	private function getCookieProfile ()
	{
		# End if none
		if (!isSet ($_COOKIE['daterange'])) {return false;}
		
		# Validate the cookie
		if (substr_count ($_COOKIE[$this->settings['daterangeCookieName']], $this->settings['daterangeCookieSeparator']) != 2) {
			unset ($_COOKIE[$this->settings['daterangeCookieName']]);	// Delete the invalid cookie
			return false;
		}
		
		# Assemble the values
		$data = array ();
		list ($data['startDate'], $data['weeksAhead'], $data['updatedAt']) = explode ($this->settings['daterangeCookieSeparator'], $_COOKIE[$this->settings['daterangeCookieName']]);
		
		# Validate the profile
		if (!$this->validProfile ($data)) {
			unset ($_COOKIE[$this->settings['daterangeCookieName']]);	// Delete the invalid cookie
			return false;
		}
		
		# Return the data
		return $data;
	}
	
	
	# Function to validate a profile
	private function validProfile ($data)
	{
		# Ensure the date is valid
		if (!preg_match ('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/', $data['startDate'], $matches)) {return false;}
		if (!checkdate ($matches[2], $matches[3], $matches[1])) {return false;}
		
		# Ensure the weeksAhead is not too great
		if ($data['weeksAhead'] > $this->settings['filteringControlWeeksAheadMax']) {return false;}
		
		# Ensure the timestamp is numeric
		if (!ctype_digit ($data['updatedAt'])) {return false;}
		
		# Return valid status
		return true;
	}
	
	
	# Function to save the implicit view dates to the user profile
	private function saveImplicitViewDates ($startDate, $weeksAhead)
	{
		# Set a cookie with this value
		$cookieValue = $startDate . $this->settings['daterangeCookieSeparator'] . $weeksAhead . $this->settings['daterangeCookieSeparator'] . time ();
		$thirtyDays = 7 * 24 * 60 * 60;
		setcookie ($this->settings['daterangeCookieName'], $cookieValue, time () + $thirtyDays, $this->baseUrl . '/', $_SERVER['SERVER_NAME']);
		
		# If there is a user, add/update the view dates into their profile
		if ($this->user) {
			$profile = array (
				'id' => $this->user,
				'startDate' => $startDate,
				'weeksAhead' => $weeksAhead,
			);
			$this->databaseConnection->insert ($this->settings['database'], 'users', $profile, $onDuplicateKeyUpdate = true);
		}
	}
	
	
	# Function to create a date-range panel
	private function dateRangePanel ()
	{
		# Start the HTML
		$html = '';
		
		# If a reset is requested, reset the profile dates, and refresh the page to prevent /reset.html staying in the URL
		#!# /reset.html always resets to the top-level of the system; it should instead be to the current view, e.g. /rooms/reception/reset.html
		if (isSet ($_GET['reset']) && $_GET['reset'] == '1') {
			$this->saveImplicitViewDates ($this->defaultDateState['startDate'], $this->defaultDateState['weeksAhead']);
			$url = $_SERVER['_SITE_URL'] . $_SERVER['SCRIPT_NAME'];
			$url = preg_replace ('|/reset.html$|', '/', $url);
			$html = application::sendHeader (302, $url, $redirectMessage = true);
			return $html;
		}
		
		# Define an array of weeks
		$weeks = array ();
		for ($i = 1; $i <= $this->settings['filteringControlWeeksAheadMax']; $i++) {
			$weeks[$i] = $i . ($i == 1 ? ' week' : ' weeks');
		}
		
		# Create a form
		#!# Replace spaces with CSS
		$clearanceHtml = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;or&nbsp;<a class="small" href="' . $this->baseUrl . '/reset.html' . '">reset</a></p>';
		$form = new form (array (
			'displayRestrictions' => false,
			'name' => 'datefilter',
			'nullText' => false,
			'div' => 'ultimateform',
			'display' => 'template',
			'displayTemplate' => "{[[PROBLEMS]]}\n\t\t<p>" . '<span class="success">Filtering</span> below dates for:<br />{weeksAhead} from {startDate} {[[SUBMIT]]}' . $clearanceHtml,
			'submitButtonText' => 'Go!',
			'submitButtonAccesskey' => false,
			'formCompleteText' => false,
			'requiredFieldIndicator' => false,
			'reappear' => true,
			'jQuery' => false,		// Already loaded on the page
		));
		$form->datetime ($this->datePickerAttributes () + array (
			'name'			=> 'startDate',
			'title'			=> 'Start date',
			'required' 		=> true,
			'default'		=> $this->userProfile['startDate'],
		));
		$form->select (array (
			'name' => 'weeksAhead',
			'title' => 'Weeks ahead',
			'values' => $weeks,
			'default' => $this->userProfile['weeksAhead'],
			'required' => true,
		));
		
		# Process the form
		if ($result = $form->process ($html)) {
			
			# Insert/update the dates into the user's profile
			$this->saveImplicitViewDates ($result['startDate'], $result['weeksAhead']);
			
			# Refresh the page, which will pick up the newly-saved dates in the user's profile
			$html = application::sendHeader ('refresh', false, $redirectMessage = true);
		}
		
		# Surround with a div
		$html = "\n<div class=\"datefilter\">" . $html . "\n</div>";
		
		# Return the HTML
		return $html;
	}
	
	
	
	/* ---------------------------- */
	/*        Listing functions     */
	/* ---------------------------- */
	
	
	# Function to show 'My' timetable
	public function my ()
	{
		# Not yet available
		echo "<div class=\"graybox\"><p class=\"warning\"><strong>This section is not yet available. It will shortly enable you to subscribe to particular areas of activity in the {$this->settings['institutionDescription']}.</strong></p></div>";
	}
	
	
	# General timetable browsing page
	public function browse ()
	{
		# Get the HTML from the browsing listing
		$html = $this->browsingListing ();
		
		# Show the HTML
		echo $html;
	}
	
	
	# Browsing listing
	private function browsingListing ($forcedParameters = array (), $actualId = NULL, $class = false)
	{
		# Start the HTML
		$html = '';
		
		# By default, the filter parameters are just the $_GET array, but merge in any forced parameters
		$filterParameters = array_merge ($_GET, $forcedParameters);
		
		# If an actual ID is supplied (i.e. the underlying ID represented by a moniker in the URL, substitute that in
		if (!is_null ($actualId)) {
			if (array_key_exists ('id', $filterParameters)) {
				$filterParameters['id'] = $actualId;
			}
		}
		
		# Determine limitations, by parsing the URL
		list ($where, $linkableParameters, $listing, $forceListing, $breadcrumbEntries, $typeDescription, $highlightBookings, $introductoryText) = $this->whereClauses ($filterParameters);
		
		# End if the limitation parsing found any errors, e.g. invalid dates, invalid weeks, etc.
		if (!is_array ($where)) {
			#!# 404 page?
			$html = "\n<p>The web address (URL) you gave was not valid. Please check and try again.</p>";
			return $html;
		}
		
		# Do export instead if required; strip out date-related filtering, but set the current date as the start date
		if ($this->export) {
			if ($this->export === true) {
				$html .= $this->exportingPage ();
				return $html;
			}
			$where = $this->whereFilteringNoDate ($where);
			$where['date_implicit_from'] = "`date` >= '" . date ('Y-m-d') . "'";
			$bookings = $this->getBookings ($where);
			$title = end ($breadcrumbEntries);
			$this->exportBookings ($bookings, $this->export, $title);
			return;
		}
		
		# Show the 'You are in...' breadcrumb trail
		$html .= $this->breadcrumbTrail ($breadcrumbEntries);
		
		# Show introductory text if required
		if ($introductoryText) {
			$html .= $introductoryText;
		}
		
		# If a listing has been created, show it
		if ($listing) {
			if ($forceListing) {
				$html .= $listing;
			} else {
				$html .= "\n" . '<script type="text/javascript" src="/sitetech/collapsable.js"></script>';
				$html .= "\n" . '<dl class="collapsable faq">';
				$html .= "\n\t" . "<dt>Or click to view and select {$typeDescription}&hellip;</dt>";
				$html .= "\n\t" . '<dd>' . $listing . '</dd>';
				$html .= "\n" . '</dl>';
			}
		}
		
		# If a listing is forced (i.e. the user must select further from a list) rather being than a helpful listing above the table, end here
		if ($forceListing) {
			return $html;
		}
		
		# Add a link to limit if no limitations already in place
		if (!$where) {
			$html .= "<p>&hellip; or <a href=\"{$this->baseUrl}/\">limit by certain categories</a>.</p>";
		}
		
		# Get the bookings
		$bookings = $this->getBookings ($where);
		
		# Create the listing
		$html .= $this->createListing ($bookings, $where, $linkableParameters, $highlightBookings);
		
		# Surround with a box if required
		if ($class) {
			$html = "\n<div class=\"{$class}\">" . $html . '</div>';
		}
		
		# Return the HTML
		return $html;
	}
	
	
	# Function to parse the URL for WHERE limitations; returns an array (possibly empty) if the URL is valid
	private function whereClauses ($filterParameters)
	{
		# Start an array of WHERE limitations; those involving date-based limitation must have keys prefixed with date_ so that non-date-related keys do not crash the seededDates lookup (which has only a date field)
		$where = array ();
		
		# Set a default type description (e.g. 'weeks', 'people', etc.)
		$typeDescription = 'others';
		
		# Set other default return values
		$linkableParameters = array ();
		$listing = array ();
		$forceListing = false;
		$breadcrumbEntries = array ();
		$highlightBookings = false;
		$introductoryText = false;
		
		# Set to add implicit dates by default
		$useImplicitViewDates = true;
		
		# For clarity, remove the global action as this is never valid
		if (isSet ($filterParameters['action'])) {unset ($filterParameters['action']);}
		
		# Activities, people and rooms (only one permitted at a time)
		#!# Refactor this block to combine the clauses when the implementation of userId lookups is done
		if (isSet ($filterParameters['object']) && strlen ($filterParameters['object']) && isSet ($filterParameters['id']) && strlen ($filterParameters['id'])) {	// These use id=... rather than 
			$objectType = $filterParameters['object'];
			switch ($objectType) {
				
				# Bookings
				case 'bookings':
					
					# Parse the booking URL to get a list of numeric IDs, e.g. '1' or '14-16' or '1,24-6' (meaning 1,24,25,26)
					if (!$ids = application::parseRangeList ($filterParameters['id'], $errorMessage)) {return false;}
					
					# Get the items
					$whereBookingsList = array ();
					$whereBookingsList[] = 'bookings.id IN (' . implode (',', $ids) . ')';
					$whereBookingsList['suppressedFromListingsByDefault'] = false;	// i.e. do not perform this check
					if ($items = $this->getBookings ($whereBookingsList)) {		// Validate the booking(s) exists
						$totalFound = count ($items);
						$firstBookingId = key ($items);
						reset ($items);
						$name = ($totalFound == 1 ? 'Booking #' . $firstBookingId : "{$totalFound} selected bookings");
						$where[$objectType] = '1=1';
						$where['suppressedFromListingsByDefault'] = false;	// i.e. do not perform this check
						$breadcrumbEntries = $this->registerBreadcrumbEntries ($breadcrumbEntries, $objectType, $name);
						$typeDescription = 'other bookings';
						$highlightBookings = array_keys ($items);
						$introductoryText = "\n<p>" . ($totalFound == 1 ? "The booking, #{$firstBookingId}, is" : "The {$totalFound} selected bookings are") . ' shown <span class="highlighted">highlighted</span> below amongst any other bookings.</p>';
						
						# Determine the date range
						$useImplicitViewDates = false;
						list ($startDate, $untilDate) = $this->getDateRangeOfBookings ($items);
						$where['date_term'] = "`date` >= '" . $this->mondayOfDate ($startDate) . "'";
						$where['date_until'] = "`date` <= '" . $this->fridayOfDate ($untilDate) . "'";
					}
					if (!isSet ($where[$objectType])) {return false;}	// Invalid URL, so cancel all matches so far
					break;
					
				# Activities
				case 'activities':
					if ($item = $this->getActivities ($filterParameters['id'])) {		// Validate the activity exists
						$activitiesFamilyIds = $this->getActivities (false, $item['id']);
						$activitiesFamilyIdsQuoted = array ();
						foreach ($activitiesFamilyIds as $activitiesFamilyId => $activity) {
							$activitiesFamilyIdsQuoted[] = $this->databaseConnection->quote ($activitiesFamilyId);
						}
						$where[$objectType] = 'areaOfActivityId IN (' . implode (',', $activitiesFamilyIdsQuoted) . ')';
						$linkableParameters['areaOfActivityId'] = $item['id'];
						$listing = $this->activitiesLinks ($filterParameters['id']);
						$breadcrumbEntries = $this->registerBreadcrumbEntries ($breadcrumbEntries, $objectType, $item['name']);
						$typeDescription = 'another activity';
					}
					if (!isSet ($where[$objectType])) {return false;}	// Invalid URL, so cancel all matches so far
					break;
					
				# People
				case 'people':
					if ($item = $this->getPeople ($filterParameters['id'])) {		// Validate the person exists
						$where[$objectType] = $this->personClashCheckingWhereClause ($item['id']);
						$linkableParameters['bookedForUserid'] = $item['id'];
						$listing = $this->peopleLinks ();
						$breadcrumbEntries = $this->registerBreadcrumbEntries ($breadcrumbEntries, $objectType, $item['name']);
						$typeDescription = 'someone else';
					}
					if (!isSet ($where[$objectType])) {return false;}	// Invalid URL, so cancel all matches so far
					break;
					
				# Rooms
				case 'rooms':
					if ($item = $this->getRooms ($filterParameters['id'])) {		// Validate the room exists
						$where[$objectType] = 'roomId = ' . $this->databaseConnection->quote ($item['id']);
						$linkableParameters['roomId'] = $item['id'];
						$listing = $this->roomsLinks ();
						$breadcrumbEntries = $this->registerBreadcrumbEntries ($breadcrumbEntries, $objectType, $item['name']);
						$typeDescription = 'another room';
						$where['suppressedFromListingsByDefault'] = false;	// i.e. do not perform this check
					}
					if (!isSet ($where[$objectType])) {return false;}	// Invalid URL, so cancel all matches so far
					break;
			}
		}
		
		#!# Breadcrumb entries missing for the date/based ones
		
		# Explicit filter dates
		if (isSet ($filterParameters['datefilter_start']) && isSet ($filterParameters['datefilter_until'])) {
			$where['date_start'] = "`date` >= '" . $this->mondayOfDate ($filterParameters['datefilter_start']) . "'";
			$where['date_until'] = "`date` <= '" . $this->fridayOfDate ($filterParameters['datefilter_until']) . "'";
			$useImplicitViewDates = false;
		}
		if (!isSet ($where['date_start'])) {
			
			# Year
			if (isSet ($filterParameters['year'])) {
				if (preg_match ('/^20[0-3][0-9]$/', $filterParameters['year'])) {
					$useImplicitViewDates = false;
					$where['date_year'] = "YEAR(`date`) = {$filterParameters['year']}";
					$listing = $this->monthLinks ($filterParameters['year']);
					$forceListing = true;
				}
				if (!isSet ($where['date_year'])) {return false;}	// Invalid URL, so cancel all matches so far
			}
			
			# Month, which requires year
			if (isSet ($where['date_year'])) {
				if (isSet ($filterParameters['month'])) {
					if (in_array ($filterParameters['month'], $this->months)) {
						$monthNumber = array_search ($filterParameters['month'], $this->months);
						$where['date_month'] = "MONTH(`date`) = {$monthNumber}";
						$forceListing = false;
						$typeDescription = 'another month';
					}
					if (!isSet ($where['date_month'])) {return false;}	// Invalid URL, so cancel all matches so far
				}
			}
			
			# Day, which requires year and month
			if (isSet ($where['date_year']) && isSet ($where['date_month'])) {
				if (isSet ($filterParameters['day'])) {
					if (ctype_digit ($filterParameters['day']) && ($filterParameters['day'] > 0) && ($filterParameters['day'] <= 32)) {
						#!# Also need to ensure the date is in the seed list (or use another testing metric), so that dates like 1968 or 2040 are treated as invalid URLs
						if (checkdate ($monthNumber, $filterParameters['day'], $filterParameters['year'])) {
							$where['date_day'] = "DAYOFMONTH(`date`) = {$filterParameters['day']}";
							$listing = $this->dayLinks ($filterParameters['year'], $monthNumber);
							$typeDescription = 'another day';
						}
					}
					if (!isSet ($where['date_day'])) {return false;}	// Invalid URL, so cancel all matches so far
				}
			}
			
			# Process custom year support if a natural date is not found; custom year cannot co-exist with custom date formats
			if (!isSet ($where['date_year'])) {
				
				# Add functions for custom year support, if enabled
				if ($this->settings['customYearLabel']) {
					
					# Custom year (label representing a range)
					if (isSet ($filterParameters['customyear'])) {
						if (preg_match ('/^20([0-3][0-9])-([0-3][0-9])$/', $filterParameters['customyear'], $matches)) {
							if ($matches[1] + 1 == $matches[2]) {	// Ensure they are incremental, e.g. 2019-20 not 2019-21
								$useImplicitViewDates = false;
								$where['date_customyear'] = "((YEAR(`date`) = 20{$matches[1]} AND MONTH(`date`) >= {$this->settings['startingMonthCustomYear']}) OR (YEAR(`date`) = 20{$matches[2]} AND MONTH(`date`) < {$this->settings['startingMonthCustomYear']}))";
								$listing = $this->termLinks ($filterParameters['customyear']);
								$forceListing = true;
							}
						}
						if (!isSet ($where['date_customyear'])) {return false;}	// Invalid URL, so cancel all matches so far
					}
					
					# Term, which requires custom year
					if (isSet ($where['date_customyear'])) {
						if (isSet ($filterParameters['term'])) {
							$termLabels = $this->getTermLabels ();
							if (in_array ($filterParameters['term'], $termLabels)) {
								if (!$term = $this->getTerm ($filterParameters['customyear'], $filterParameters['term'])) {
									return false;	// Invalid or not yet present in the data
								}
								$where['date_term'] = "`date` >= '" . $this->mondayOfDate ($term['startDate']) . "'";
								$where['date_until'] = "`date` <= '" . $this->fridayOfDate ($term['untilDate']) . "'";
								unset ($where['date_customyear']);	// Not necessary as the term is more specific
								$customWeeks = $this->getCustomWeeks ($filterParameters['customyear'], $filterParameters['term']);
								$listing = $this->customWeekLinks ($customWeeks);
								$forceListing = false;
								#!# Question of inconsistency here - this shows the container weeks but should also have other terms
								$typeDescription = 'a week in this term';
							}
							if (!isSet ($where['date_term'])) {return false;}	// Invalid URL, so cancel all matches so far
						}
					}
					
					# Named week (e.g. week1), which requires term (and, by implication, a custom year)
					if (isSet ($where['date_term'])) {
						if (isSet ($filterParameters['customweek'])) {
							if (preg_match ('/^([0-9]+)$/', $filterParameters['customweek'], $matches)) {
								if (isSet ($customWeeks[$filterParameters['customweek']])) {
									$customWeek = $customWeeks[$filterParameters['customweek']];
									$where['date_customweek'] = "`date` >= '" . $this->mondayOfDate ($customWeek['startDate']) . "'";
									$where['date_until'] = "`date` <= '" . $this->fridayOfDate ($customWeek['untilDate']) . "'";
									unset ($where['date_term']);	// Not necessary as the named week is more specific (and in any case may not be exactly within the term)
									$listing = $this->customWeekLinks ($customWeeks);
									$typeDescription = 'another week in this term';
								}
							}
							if (!isSet ($where['date_customweek'])) {return false;}	// Invalid URL, so cancel all matches so far
						}
					}
				}
			}
		}
		
		# Add in implicit dates, if required, using the user's profile where possible
		if ($useImplicitViewDates) {
			$where['date_implicit_from']  = "`date` >= '" . $this->mondayOfDate ($this->userProfile['startDate']) . "'";	// This is done in PHP rather than using MySQL's NOW() as NOW() results in a query cache miss
			$where['date_implicit_until'] = "`date` <= '" . $this->fridayOfDate ($this->userProfile['untilDate']) . "'";
		}
		
		//application::dumpData ($filterParameters);
		//application::dumpData ($where);
		//application::dumpData ($linkableParameters);
		
		# Return the data
		return array ($where, $linkableParameters, $listing, $forceListing, $breadcrumbEntries, $typeDescription, $highlightBookings, $introductoryText);
	}
	
	
	# Function to convert a date to being at the start of the week; see http://stackoverflow.com/a/2958859/180733
	private function mondayOfDate ($dateOrTime, $isTime = false)
	{
		# Convert to a Monday
		$dateTimestamp = ($isTime ? $dateOrTime : strtotime ($dateOrTime . ' 01:01:00'));
		$oneDay = 24*60*60;
		$monday = date ('Y-m-d', $dateTimestamp - ((date ('w', $dateTimestamp) - 1) * $oneDay));
		
		# Return the date
		return $monday;
	}
	
	
	# Function to convert a date to being at the end of the week
	private function fridayOfDate ($dateOrTime, $isTime = false)
	{
		# Convert to a Friday
		$dateTimestamp = ($isTime ? $dateOrTime : strtotime ($dateOrTime . ' 01:01:00'));
		$oneDay = 24*60*60;
		#!# If the startingDayCustomWeek is 1 (i.e. Monday), this still ends up with the Friday in the following week, not the current week; these two algorithms need to be replaced as they are not clear
		$friday  = date ('Y-m-d', (($dateTimestamp - ((date ('w', $dateTimestamp)  - 1) * $oneDay) + (4 /* i.e. 4 days representing the difference of Monday to Friday */ * $oneDay))));
		
		# Return the date
		return $friday;
	}
	
	
	# Function to create a WHERE clause for clash checking of a person
	private function personClashCheckingWhereClause ($userIdOrIds)
	{
		# Start a list of subclauses
		$subclauses = array ();
		
		# Convert to an array
		$userIds = (is_array ($userIdOrIds) ? $userIdOrIds : array ($userIdOrIds));
		
		# Start with a standard check of the user being directly booked
		foreach ($userIds as $userId) {
			$subclauses[] = 'bookedForUserid LIKE ' . $this->databaseConnection->quote ('%|' . $userId . '|%');
		}
		
		# Now deal with the areas of activity they are involved in
		if ($areasOfActivityInvolvedIds = $this->getAreasOfActivityInvolved ($userIds)) {
			$subclauses[] = 'areaOfActivityId IN (' . implode (',', $areasOfActivityInvolvedIds) . ')';	// These are numeric so do not need to be quote
		}
		
		# Compile the result
		$sql = implode (' OR ', $subclauses);
		
		# If there is more than one, add parentheses to avoid ambiguity
		if (count ($subclauses) > 1) {
			$sql = '(' . $sql . ')';
		}
		
		# Return the SQL extract
		return $sql;
	}
	
	
	# Function to get the areas of activity that a person is, or people are, routinely involved in
	private function getAreasOfActivityInvolved ($userIdOrIds)
	{
		# Convert to an array
		$userIds = (is_array ($userIdOrIds) ? $userIdOrIds : array ($userIdOrIds));
		
		# Determine the subclauses
		$subclauses = array ();
		foreach ($userIds as $userId) {
			$subclauses[] = 'people LIKE ' . $this->databaseConnection->quote ('%|' . $userId . '|%');
		}
		
		# Get the data
		$query = "SELECT id FROM {$this->settings['database']}.areaOfActivity WHERE (" . implode (' OR ', $subclauses) . ')';
		$areasOfActivityIds = $this->databaseConnection->getPairs ($query);
		
		# Return the result
		return $areasOfActivityIds;
	}
	
	
	# Function to get a date range from a set of bookings
	private function getDateRangeOfBookings ($bookings)
	{
		# Get the start date, by just looking this up from the first item in the bookings list (which will be in order of startDate)
		$firstBookingId = key ($bookings);
		reset ($bookings);
		$startDate = $bookings[$firstBookingId]['startDate'];
		
		# Get the end date, by looping through each booking and finding the latest untilTime, then converting that to a date; it is not safe to take the latest item in the list because an earlier item may end later
		$latestUntilTime = $bookings[$firstBookingId]['untilTime'];	// Start with the first in the list
		foreach ($bookings as $id => $booking) {
			if ($booking['untilTime'] > $latestUntilTime) {
				$latestUntilTime = $booking['untilTime'];
			}
		}
		$untilDate = date ('Y-m-d', $latestUntilTime);
		
		# Return the list
		return array ($startDate, $untilDate);
	}
	
	
	# Function to get a date range from a set of bookings arranged in the bookingsByWeekThenDay format
	private function getDateRangeOfBookingsFromBookingsByWeekThenDay ($bookingsByWeekThenDay)
	{
		# Get the start date, which is the first key
		$startDate = key ($bookingsByWeekThenDay);
		reset ($bookingsByWeekThenDay);
		
		# Get the end date, by finding the last item
		$lastWeek = end ($bookingsByWeekThenDay);
		$datesOfLastWeek = array_keys ($lastWeek);	// Temporary variable has to be used, i.e. can't directly put in array_pop, because array_pop modifies its argument directly: http://stackoverflow.com/a/2967629
		$untilDate = array_pop ($datesOfLastWeek);
		
		# Return the list
		return array ($startDate, $untilDate);
	}
	
	
	# Function to register breacrumb entries
	private function registerBreadcrumbEntries ($breadcrumbEntries, $action, $item)
	{
		# Look up the action
		$actionProperties = $this->actions[$action];
		
		# Assemble the URL
		$url = '/' . $actionProperties['url'];
		
		# Register the entry
		$breadcrumbEntries[$url] = $actionProperties['description'];
		
		# Add the item itself, which has no URL
		$breadcrumbEntries[''] = $item;
		
		# Return the modified list
		return $breadcrumbEntries;
	}
	
	
	# Function to assemble a breadcrumb trail
	private function breadcrumbTrail ($entries)
	{
		# End if no entries
		if (!$entries) {return false;}
		
		# Start the HTML
		$html = '';
		
		# Start a list
		$list = array ();
		
		# Add the root node
		$frontPage = array ('/' => 'Timetable');
		$entries = array_merge ($frontPage, $entries);
		
		# Add each entry
		foreach ($entries as $url => $entry) {
			$label = htmlspecialchars ($entry);
			if ($url) {
				$url = $this->baseUrl . $url;
				$list[] = "<a href=\"{$url}\">{$label}</a>";
			} else {
				$list[] = "<strong>{$label}</strong>";
			}
		}
		
		# Compile the HTML
		$html = "\n<p>You are currently viewing: " . implode (' &raquo; ', $list) . '</p>';
		
		# Return the HTML
		return $html;
	}
	
	
	# Function to get a list of year links
	private function yearLinks ($customYearMode = false)
	{
		# Determine the current year; in custom year mode rewind back to the start of the current custom year if necessary
		$currentYear = date ('Y');
		if ($customYearMode) {
			$currentMonth = date ('n');
			if ($currentMonth < $this->settings['startingMonthCustomYear']) {
				$currentYear = $currentYear - 1;
			}
		}
		
		# Determine the start year, namely the previous year
		$startYear = $currentYear - $this->settings['yearsBehindBookable'];
		
		# Show the specified years ahead, namely previous, plus current year, plus ahead
		$totalYears = $this->settings['yearsBehindBookable'] + 1 + $this->settings['yearsAheadBookable'];
		
		# Create the list
		$list = array ();
		for ($i = 0; $i < $totalYears; $i++) {
			$year = $startYear + $i;
			$customYearSuffix = ($customYearMode ? '-' . substr (($year + 1), -2) : '');
			$url = "{$this->baseUrl}/{$year}{$customYearSuffix}/";
			$label = $year . $customYearSuffix;
			if ($year == $currentYear) {$label = "<strong>{$label}</strong>";}
			$list[] = "<a href=\"{$url}\">" . $label . '</a>';
		}
		
		# Compile the HTML
		$html = application::htmlUl ($list);
		
		# Return the HTML
		return $html;
	}
	
	
	# Function to get a list of month links
	private function monthLinks ($year)
	{
		# Get the current month and year
		$currentMonth = date ('n');
		$currentYear = date ('Y');
		
		# Create the list
		$list = array ();
		foreach ($this->months as $number => $month) {
			$url = "{$this->baseUrl}/{$year}/{$month}/";
			$label = ucfirst ($month) . ' ' . $year;
			if (($currentYear == $year) && ($currentMonth == $number)) {$label = "<strong>{$label}</strong>";}
			$list[] = "<a href=\"{$url}\">" . $label . '</a>';
		}
		
		# Compile the HTML
		$html = application::htmlUl ($list);
		
		# Return the HTML
		return $html;
	}
	
	
	# Function to get a list of day links
	private function dayLinks ($year, $month)
	{
		# Determine the number of days in a month
		$daysInMonth = cal_days_in_month (CAL_GREGORIAN, $month, $year);
		
		# Determine today
		$today = date ('Y-m-d');
		
		# Create the list
		$list = array ();
		for ($day = 1; $day <= $daysInMonth; $day++) {
			$url = "{$this->baseUrl}/{$year}/{$this->months[$month]}/{$day}/";
			$timestamp = mktime (1, 1, 1, $month, $day, $year);
			$label = date ('l, jS F Y', $timestamp);
			$dayIso = date ('Y-m-d', $timestamp);
			if ($dayIso == $today) {$label = "<strong>{$label}</strong>";}
			$list[] = "<a href=\"{$url}\">" . $label . '</a>';
		}
		
		# Compile the HTML
		$html = application::htmlUl ($list);
		
		# Return the HTML
		return $html;
	}
	
	
	# Function to get a list of term links
	private function termLinks ($customYear)
	{
		# Get the terms
		$allTerms = $this->getTerms ();
		
		# Regroup by customYear
		$allTerms = application::regroup ($allTerms, 'customYear', false);
		
		# End if there are no terms for the current custom year
		if (!isSet ($allTerms[$customYear])) {
			list ($startYear, $endYear2digits) = explode ('-', $customYear);
			$html  = "\n<p>The term dates for the requested have not yet been defined, so this listing is not available.</p>";
			$html .= ($this->userIsEditor ? "\n<p>As an Editor, you can <a href=\"{$this->baseUrl}/terms/add.html?startYear={$startYear}&amp;endYear=20{$endYear2digits}\">add the term dates</a>.</p>" : '');
			return $html;
		}
		
		# Create the list
		$list = array ();
		$terms = $allTerms[$customYear];
		foreach ($terms as $id => $term) {
			$url = "{$this->baseUrl}/{$id}/";
			$label = $term['name'];
			if ($term['isCurrent']) {$label = "<strong>{$label}</strong>";}
			$list[] = "<a href=\"{$url}\">" . $label . '</a>';
		}
		
		# Compile the HTML
		$html = application::htmlUl ($list);
		
		# Return the HTML
		return $html;
	}
	
	
	# Function to get a list of week links
	private function customWeekLinks ($customWeeks)
	{
		# Create the list
		$list = array ();
		foreach ($customWeeks as $weekNumber => $customWeek) {
			$label = $customWeek['label'];
			if ($customWeek['isCurrent']) {$label = "<strong>{$label}</strong>";}
			$list[] = "<a href=\"{$customWeek['url']}\">" . $label . '</a>';
		}
		
		# Compile the HTML
		$html = application::htmlUl ($list);
		
		# Return the HTML
		return $html;
	}
	
	
	# Function to get the start dates of all custom weeks
	private function getAllCustomWeekStartDates ($bookingsByWeekThenDay)
	{
		# Determine the outer range of the shown bookings, to avoid unnecessarily fetching week data that will not be used
		list ($startDate, $untilDate) = $this->getDateRangeOfBookingsFromBookingsByWeekThenDay ($bookingsByWeekThenDay);
		
		# Get the terms
		if (!$terms = $this->getTerms ($startDate, $untilDate)) {return array ();}
		
		# Get the custom weeks for each term
		$dates = array ();
		foreach ($terms as $term) {
			$weeks = $this->getCustomWeeks ($term['customYear'], $term['termLabel']);
			foreach ($weeks as $week) {
				$startDate = $week['startDate'];
				$label = $week['label'];
				$dates[$startDate] = $label;	// Will ensure uniqueness
			}
		}
		
		# Sort and unique
		ksort ($dates);
		
		# Return the list
		return $dates;
	}
	
	
	# Function to get the current term's named weeks
	private function getCustomWeeks ($customYear, $term)
	{
		# Determine the day of the first day of the first week, as a timestamp
		$startDateTimestamp = $this->getFirstCustomWeekStartDateTimestamp ($customYear, $term);
		
		# Determine today
		$today = date ('Y-m-d');
		
		# Determine the current named weeks
		$customWeeks = array ();
		$weekNumber = $this->settings['weeksInTermFirstNumber'];
		for ($i = 0; $i < $this->settings['weeksInTerm']; $i++) {
			
			# Set the name
			$name = 'Week ' . $weekNumber;
			
			# Determine the startDate and untilDate for each
			$startTimestamp = $startDateTimestamp + ($i * 60*60*24*7);
			$startDate = date ('Y-m-d', $startTimestamp);
			$untilDate = date ('Y-m-d', $startTimestamp + (60*60*24*6));
			$dateRangeString = date ('jS F', strtotime ($startDate)) . ' to ' . date ('jS F Y', strtotime ($untilDate));
			
			# Assemble the data
			$customWeeks[$weekNumber] = array (
				'name' => $name,
				'url' => "{$this->baseUrl}/{$customYear}/{$term}/week{$weekNumber}/",
				'startDate' => $startDate,
				'untilDate' => $untilDate,
				'isCurrent' => (($today >= $startDate) && ($today <= $untilDate)),
				'label' => $name . ' (' . $dateRangeString . ')' . ', ' . ucfirst ($term) . ' term ' . $customYear,
				'labelShort' => $name,
			);
			
			# Increment to the next week
			$weekNumber++;
		}
		
		# Return the list
		return $customWeeks;
	}
	
	
	# Function to get a list of activity links
	#!# Some duplicated code - ideally this function should not exist and the switch in crudEditing() would cope, but it is needed for the front page
	private function activitiesLinks ($currentNodeId = false, $editorsSeeLinks = true)
	{
		# Start the HTML
		$html = '';
		
		# Get the activities hierarchy
		$this->activitiesHierarchy = $this->getActivities ();
		
		# Create as a hierarchy
		$html .= "\n<p><strong>Click on an area of activity to view the bookings relevant to it.</strong></p>";
		$html .= "\n<p>Those areas of activity shown <span class=\"comment\">grayed out</span> are marked as older types not available for new bookings.</p>";
		if ($this->userIsEditor && $editorsSeeLinks) {
			$html .= "\n<p>As an Editor, you can click on the [edit] link to edit its details, or [+] to add a new area of activity within that item.</p>";
		}
		// $html .= '<script type="text/javascript" src="/sitetech/pde.js"></script>';
		#!# Need to be able to specify that some sections, e.g. year groups under the main taught course section have an expandable arrow by default
		$html .= hierarchy::asUl ($this->activitiesHierarchy, "{$this->baseUrl}/activities/", ($this->userIsEditor && $editorsSeeLinks ? 'add.html?parent=%s' : ''), ($this->userIsEditor && $editorsSeeLinks ? '%s/edit.html' : ''), 'hideFromNew', $currentNodeId /*, 'hierarchicallisting pde' */);
		
		# Return the HTML
		return $html;
	}
	
	
	# Function to get a list of person links
	private function peopleLinks ()
	{
		# Start the HTML
		$html = '';
		
		# Determine if in 'all' people mode (rather than just recent)
		$mode = (isSet ($_GET['mode']) && $_GET['mode'] == 'all' ? 'all' : 'recent');
		
		# Create tabs
		$tabs = array (
			'recent'	=> "<a href=\"{$this->baseUrl}/people/\"><img src=\"/images/icons/clock.png\" alt=\"\" /> Recent (have bookings in last year)</a>",
			'all'		=> "<a href=\"{$this->baseUrl}/people/all.html\"><img src=\"/images/icons/clock_red.png\" alt=\"\" /> Everyone</a>",
		);
		$html .= application::htmlUl ($tabs, 0, 'tabs small', true, false, false, false, $mode);
		
		# Get the list of people
		$data = $this->getPeople (false, true, true, false, false, ($this->userIsEditor), $recentYears = ($mode == 'recent' ? 1 : false));
		
		# End if no data
		if (!$data) {
			#!# Refactor this section - is duplicated and hacky
			$description = strtolower ($this->actions[$this->action]['description']);
			$html .= "\n<p>There are no people entries so far." . '</p>';
			return $html;
		}
		
		# Compile the HTML
		$html .= application::htmlUl ($data);
		
		# Return the HTML
		return $html;
	}
	
	
	# Function to get a list of room links
	private function roomsLinks ()
	{
		# Start the HTML
		$html = '';
		
		# Get the list of rooms
		$data = $this->getRooms ();
		
		# End if no data
		if (!$data) {
			#!# Refactor this section - is duplicated and hacky
			$description = strtolower ($this->actions[$this->action]['description']);
			$html .= "\n<p>There are no rooms entries so far." . ($this->userIsEditor ? " You may wish to <a href=\"{$this->baseUrl}/rooms/add.html\">add one</a>." : '') . '</p>';
			return $html;
		}
		
		# Convert to a nested list
		$list = array ();
		foreach ($data as $type => $buildings) {
			$buildingsList = array ();
			foreach ($buildings as $building => $rooms) {
				$roomsList = array ();
				foreach ($rooms as $id => $name) {
					$key = htmlspecialchars ($id);
					$label = htmlspecialchars ($name);
					if (isSet ($_GET['rooms']) && ($_GET['rooms'] == $key)) {$label = "<strong>{$label}</strong>";}
					$roomsList[$id]  = "<a href=\"{$this->baseUrl}/rooms/{$key}/\">" . $label . '</a>';
					if ($this->userIsEditor) {
						$roomsList[$id] .= " &nbsp;<a class=\"minilink\" href=\"{$this->baseUrl}/rooms/{$key}/edit.html\">edit</a>";
					}
				}
				$building = (strlen ($building) ? htmlspecialchars ($building) . ':' : '<span class="faded">[No contained building]:</span>');
				$buildingsList[] = $building . application::htmlUl ($roomsList, 2);
			}
			$list[] = htmlspecialchars ($type) . ':' . application::htmlUl ($buildingsList, 1);
		}
		$html .= application::htmlUl ($list);
		
		# Return the HTML
		return $html;
	}
	
	
	# Function to get the starting day of the first day of the first custom week
	private function getFirstCustomWeekStartDateTimestamp ($customYear, $term)
	{
		# Get the term details
		$term = $this->getTerm ($customYear, $term);
		
		# Get the timestamp of the first starting day of the custom week from (or on) the start of term, e.g. the first Thursday that will be three days from a term starting on a Tuesday
		$time = strtotime ($term['startDate'] . ' 01:01:00');	// ISO-8601 has Monday as 1
		for ($i = 0; $i < 7; $i++) {
			$dayOfWeek = date ('N', $time);
			if ($dayOfWeek == $this->settings['startingDayCustomWeek']) {
				$date = date ('Y-m-d', $time);
				break;	// Stop when found
			}
			$time += (60*60*24);
		}
		
		# Return the time
		return $time;
	}
	
	
	# Function to get the bookings
	private function getBookings ($where = array ())
	{
		# If the user is not an editor, exclude draft bookings
		if (!$this->userIsEditor) {
			$where[] = '(draft != 1 OR draft IS NULL)';
		}
		
		# Suppress listings in private areas of activity by default
		if (isSet ($where['suppressedFromListingsByDefault'])) {
			unset ($where['suppressedFromListingsByDefault']);	// Remove this check
		} else {
			$where['suppressedFromListingsByDefault'] = '(suppressedFromListingsByDefault != 1 OR suppressedFromListingsByDefault IS NULL)';
		}
		
		# Construct the WHERE limitations
		$whereSql = ($where ? implode (' AND ', $where) : '1=1');
		
		# Construct the query
		$query = "
			SELECT
				bookings.id,
				date as startDate,
				UNIX_TIMESTAMP(DATE_ADD(bookings.`date`, INTERVAL bookings.startTime HOUR_SECOND)) AS startTime,
				UNIX_TIMESTAMP(DATE_ADD(bookings.`date`, INTERVAL bookings.untilTime HOUR_SECOND)) AS untilTime,
				REPLACE(LOWER(DATE_FORMAT(DATE_ADD(bookings.`date`, INTERVAL bookings.startTime HOUR_SECOND),'%l.%i%p')),'.00','') as startTimeFormatted,
				REPLACE(LOWER(DATE_FORMAT(DATE_ADD(bookings.`date`, INTERVAL bookings.untilTime HOUR_SECOND),'%l.%i%p')),'.00','') as untilTimeFormatted,
				bookings.name,
				bookings.notes,
				bookings.draft,
				areaOfActivity.id AS activityId,
				areaOfActivity.name AS activityName,
				'' AS activityNamePrefix,	/* Will be populated below */
				areaOfActivity.moniker AS activityMoniker,
				bookedForUserid,	/* This is basically a meaningless string at present - will then be replaced below by substituteUseridTokensToNames() */
				bookedByUserid,
				IF(ISNULL(peopleBookedBy.name),bookedByUserid,peopleBookedBy.name) AS bookedByUseridFormatted,
				updatedByUserid,
				IF(ISNULL(updatedBookedBy.name),updatedByUserid,updatedBookedBy.name) AS updatedByUseridFormatted,
				UNIX_TIMESTAMP(bookings.createdAt) as createdAt,
				rooms.name AS roomName,
				rooms.moniker AS roomMoniker,
				buildings.name AS buildingName,
				url
			FROM bookings
			LEFT JOIN people ON bookings.bookedForUserid = people.id
			LEFT JOIN people AS peopleBookedBy ON bookings.bookedByUserid = peopleBookedBy.id
			LEFT JOIN people AS updatedBookedBy ON bookings.updatedByUserid = updatedBookedBy.id
			LEFT JOIN rooms ON bookings.roomId = rooms.id
			LEFT JOIN buildings ON rooms.buildingId = buildings.id
			LEFT JOIN eventTypes ON bookings.eventTypeId = eventTypes.id
			LEFT JOIN areaOfActivity ON bookings.areaOfActivityId = areaOfActivity.id
			WHERE {$whereSql}
			ORDER BY startTime,untilTime	/* startTime is essential for the PHP range processing phase */
		;";
		
		# Get the data
		$bookings = $this->databaseConnection->getData ($query, "{$this->settings['database']}.bookings");
		
		// application::dumpData ($bookings);
		// application::dumpData ($this->databaseConnection->error ());
		
		# Substitute-in names
		$bookings = $this->substituteUseridTokensToNames ($bookings, array ('bookedForUserid'), true);
		
		# Add in shortnames for the area of activity
		$bookings = $this->substituteAreaOfActivityShortnames ($bookings);
		
		# Return the bookings
		return $bookings;
	}
	
	
	# Function to filter a WHERE clause list to those being date-related
	private function whereFilteringDateOnly ($where)
	{
		# Loop through and unset non-date-related
		foreach ($where as $key => $clause) {
			if (!preg_match ('/^date_/', $key)) {
				unset ($where[$key]);
			}
		}
		
		# Return the list (which may now be empty
		return $where;
	}
	
	
	# Function to filter a WHERE clause list to exclude those being date-related
	private function whereFilteringNoDate ($where)
	{
		# Loop through and unset non-date-related
		foreach ($where as $key => $clause) {
			if (preg_match ('/^date_/', $key)) {
				unset ($where[$key]);
			}
		}
		
		# Return the list (which may now be empty)
		return $where;
	}
	
	
	# Master function to show a timetable listing given a set of bookings
	private function createListing ($bookings, $where, $linkableParameters, $highlightBookings)
	{
		# Start the HTML
		$html = '';
		
		# Strip out non-date-related WHERE clauses
		$where = $this->whereFilteringDateOnly ($where);
		
		# Load required libraries
		require_once ('timedate.php');
		
		# Arrange the bookings by week and day
		$bookingsByWeekThenDay = $this->arrangeBookingsByWeekThenDay ($bookings, $where);
		
		# Determine today
		$todayIso = date ('Y-m-d');
		
		# If in day mode, show that
		$dayMode = (isSet ($where['date_day']));		// #!# Perhaps slightly brittle - needs further consideration
		if ($dayMode) {
			#!# Need to state which day
			$title = 'Bookings for day';
			$html .= $this->dayGrid ($bookings, $todayIso);
			return $html;
		}
		
		# If any are draft, state this
		if ($this->userIsEditor) {
			if ($hasDrafts = $this->hasDrafts ($bookings)) {
				$html .= "\n<p id=\"userhasdrafts\">Note: The listing below has some items which are <span class=\"draft\">not yet public</span>." . (isSet ($hasDrafts[$this->user]) ? "<br />Please <a href=\"{$this->baseUrl}/bookings/draft.html\">set your draft bookings as public</a> when you are ready." : '') . '</p>';
			}
		}
		
		# Determine if we are viewing custom week(s) that need edge chopping
		#!# Review this as it isn't being used
		// $customWeekEdgeChopping = $this->customWeekEdgeChopping ($where);
		
		# Show a date-setting panel; NB this must come immediately before the first H3 as otherwise the CSS adjacent sibling (+) selector won't match it
		$html .= $this->dateRangePanel ();
		
		# Get all custom week dates, so that they can be noted in the listing
		$customWeekStartDates = $this->getAllCustomWeekStartDates ($bookingsByWeekThenDay);
		
		# Create a listing for each week, in each format
		$formats = array (
			'grid' => 'Grid view',
			'text' => 'Text listing',
			'csv'  => 'Spreadsheet',
			'ical' => 'Calendar feed (iCal)',
		);
		$labels = array ();
		$panes = array ();
		foreach ($formats as $format => $title) {
			
			# Create the title
			$labels[$format] = $title;
			
			# Start the pane
			$panes[$format] = '';
			
			# iCal - show an export box
			if ($format == 'ical') {
				
				# Link, top-right
				$html .= "\n<div id=\"exporting\">";
				$html .= "\n<p><a rel=\"nofollow\" href=\"{$_SERVER['SCRIPT_URL']}export.html\"><img src=\"/images/icons/extras/ical.gif\" alt=\"iCal\" title=\"iCal output - export to your calendar\" /></a></p>";
				$html .= "\n</div>";
				
				$panes[$format] .= $this->exportingPage ();
				
				continue;
			}
			
			# CSV - show an export box
			if ($format == 'csv') {
				
				# Link, top-right
				$panes[$format] .= "\n<h3>Download bookings as a spreadsheet</h3>";
				$panes[$format] .= "\n<p>You can download all bookings in this section, from today onwards, below.</p>";
				$panes[$format] .= "\n<br />";
				$panes[$format] .= "\n<p><a class=\"actions\" rel=\"nofollow\" href=\"{$_SERVER['SCRIPT_URL']}timetable.csv\"><img src=\"/images/icons/page_excel.png\" alt=\"Spreadsheet\" /> Download bookings as a spreadsheet (CSV file)</a></p>";
				
				continue;
			}
			
			# For grid format, set the day row height; IE 8/9 has a margin-top bug whereby a <div margin="0"> containing paragraphs adds about 10px of margin-top to that div; oddly, IE6/7 are fine
			if ($format == 'grid') {
				$ieMarginBug = (preg_match ('/(?i)msie [8|9]/', $_SERVER['HTTP_USER_AGENT']) ? 10 : 0);
				$this->settings['dayHeightPx'] += $ieMarginBug;
				$panes[$format] .= "\n\n" . '<style type="text/css">#timetables #grid .week .day {height: ' . ($this->settings['dayHeightPx']) . 'px;}</style>' . "\n";
			}
			
			# Add the week grid for each week
			foreach ($bookingsByWeekThenDay as $startOfWeekIso => $bookingsByDay) {
				$panes[$format] .= $this->weekGrid ($startOfWeekIso, $bookingsByDay, $linkableParameters, $highlightBookings, $format, $customWeekStartDates);
			}
		}
		
		# Load into tabs
		require_once ('jquery.php');
		$jQuery = new jQuery (false, false, false, true);
		$jQuery->tabs ($labels, $panes, 0, false, false, $tabsClass = 'tabsflat');
		$html .= $jQuery->getHtml ();
		
		# Return the HTML
		return $html;
	}
	
	
	# Exporting page
	private function exportingPage ()
	{
		# Box within the tab
		require_once ('ical.php');
		$ical = new ical ();
		$icsFile = dirname ($_SERVER['SCRIPT_URL'] . 'bogus') . '/timetable.ics';	// 'bogus' ensures that e.g. /timetables/ gives /timetables/ rather than /
		$extraInstructions  = "\n" . '<p>The timetable system includes an iCal feed link in the top-right of each page.</p>';
		$extraInstructions .= "\n" . '<p>The iCal feeds will <strong>not have any date filtering</strong> applied, other than omitting previous days.</p>';
		return $ical->instructionsLink ($icsFile, $extraInstructions);
	}
	
	
	# Function to create an exported listing of bookings
	private function exportBookings ($bookings, $format, $title = false)
	{
		# Obtain the output
		$function = 'exportBookings' . ucfirst (strtolower ($format));
		$output = $this->$function ($bookings, $title);
		
		# Serve the output, cleanly buffered
		$this->bufferedOutput ($output);
	}
	
	
	# Function to create a buffered output
	private function bufferedOutput ($output)
	{
		# Flush all previous HTML (including from auto_prepend_file)
		ob_clean ();
		flush ();
		
		# Show the output
		echo $output;
		
		# End all further execution
		exit;
	}
	
	
	# Function to implement the export of bookings as iCal
	private function exportBookingsIcs ($bookings, $title = false)
	{
		# Compile the bookings data
		$events = array ();
		foreach ($bookings as $id => $booking) {
			
			# Compile description (comments) field
			$description = array ();
			if ($booking['notes']) {
				$description[] = $booking['notes'];
			}
			if ($booking['bookedForUserid']) {
				#!# Ideally this would parse out the e-mail address also
				$description[] = strip_tags ($booking['bookedForUserid']);
			}
			
			# Add the entry
			$events[$id] = array (
				'title' => $booking['name'] . ' - ' . $booking['activityName'],
				'draft' => $booking['draft'],
				'startTime' => $booking['startTime'],
				'untilTime' => $booking['untilTime'],
				'location' => $booking['roomName'] . ($booking['buildingName'] ? ', ' . $booking['buildingName'] : ''),
				'description' => ($description ? implode ('. ', $description) . '.' : ''),		// Empty string if none,
			);
		}
		
		# Delegate to iCal class
		require_once ('ical.php');
		$ical = new ical ();
		$title = ($title ? $title . ' - ' : '') . $this->settings['calendarName'];
		$icalString = $ical->create ($events, $title, 'ac.uk.cam.geog', 'Timetable');
		
		# Return the data; this is then served with output buffering
		return $icalString;
	}
	
	
	# Function to implement the export of bookings as CSV
	private function exportBookingsCsv ($bookings, $title = false)
	{
		# Order by start time
		usort ($bookings, function ($a, $b) {
			return strcmp ($a['startTime'], $b['startTime']);
		});
		
		# Remove unwanted fields, if present; each aspect (room, people) will have different fields, so it is not practical to whitelist fields, only remove unwanted
		$unwantedFields = array (
			'startTime',	// Unixtime; startTimeFormatted is human-readable
			'untilTime',	// Unixtime; untilTimeFormatted is human-readable
			'activityId',
			
		);
		foreach ($bookings as $index => $booking) {
			foreach ($unwantedFields as $unwantedField) {
				if (array_key_exists ($unwantedField, $bookings[$index])) {
					unset ($bookings[$index][$unwantedField]);
				}
			}
		}
		
		# Clear HTML, e.g. in bookedForUserid
		foreach ($bookings as $index => $booking) {
			foreach ($booking as $key => $value) {
				$bookings[$index][$key] = strip_tags ($value);
			}
		}
		
		# Format specific fields
		foreach ($bookings as $index => $booking) {
			foreach ($booking as $key => $value) {
				if ($key == 'createdAt') {
					$bookings[$index][$key] = date ('r', $value);
				}
			}
		}
		
		# Convert the bookings to CSV
		require_once ('csv.php');
		$csv = csv::dataToCsv ($bookings);
		
		# Set the filename base
		$filenameBase  = 'timetable_' . $_GET['action'] . '_' . $_GET['id'];
		$filenameBase .= '_savedAt' . date ('Ymd-His');
		
		# Publish, by sending a header and then echoing the data
		header ('Content-type: application/octet-stream');
		header ('Content-Disposition: attachment; filename="' . $filenameBase . '.csv"');
		
		# Return the data; this is then served with output buffering
		return $csv;
	}
	
	
	# Function to determine if any bookings are drafts
	private function hasDrafts ($bookings)
	{
		# Loop through each and return true if there is a draft found
		$drafts = array ();
		foreach ($bookings as $id => $booking) {
			if ($booking['draft']) {
				$bookedByUserid = $booking['bookedByUserid'];
				$drafts[$bookedByUserid] = $id;
			}
		}
		
		# Return the result
		return $drafts;
	}
	
	
	# Function to create a formatted heading
	private function weekHeading ($startOfWeekIso)
	{
		# Return the compiled string
		return "\n<h3 class=\"weekcommencing\">Week commencing " . date ('jS F Y', strtotime ($startOfWeekIso)) . '</h3>';
	}
	
	
	# Function to arrange the bookings by week then day
	private function arrangeBookingsByWeekThenDay ($bookings, $where)
	{
		# Get the dates of each week based on the bookings
		$datesOfWeeks = $this->datesOfWeeks ($bookings, $where);
		
		# Regroup the bookings by startDate
		$bookingsByDate = application::regroup ($bookings, 'startDate', false);
		
		# Start an array of the bookings
		$bookingsByWeekThenDay = array ();
		
		# Loop through each week and day, creating a structure of empty containers for each
		foreach ($datesOfWeeks as $startOfWeek => $days) {
			$bookingsByWeekThenDay[$startOfWeek] = array ();
			foreach ($days as $day) {
				$bookingsByWeekThenDay[$startOfWeek][$day] = array ();
				
				# Graft any bookings into the container
				if (isSet ($bookingsByDate[$day])) {
					$bookingsByWeekThenDay[$startOfWeek][$day] = $bookingsByDate[$day];
				}
			}
		}
		
		//application::dumpData ($where);
		//application::dumpData ($bookings);
		//application::dumpData ($datesOfWeeks);
		//application::dumpData ($bookingsByDate);
		//application::dumpData ($bookingsByWeekThenDay);
		
		# Return the bookings
		return $bookingsByWeekThenDay;
	}
	
	
	# Function to get the dates of weeks for the range of bookings supplied
	private function datesOfWeeks ($bookings, $where)
	{
		# Do a first pass to get the outer dates
		$whereSql = implode (' AND ', $where);
		$query = "
			SELECT
				UNIX_TIMESTAMP(MIN(`date`)) AS earliestBookingTime,
				UNIX_TIMESTAMP(MAX(`date`)) AS latestBookingTime
			FROM {$this->settings['database']}.seededDates
			WHERE
				{$whereSql}
		;";
		$data = $this->databaseConnection->getOne ($query);
		
		# Ensure the difference is not greater than the maximum weeks to avoid over-long pages with large amounts of data
		$oneDay = 24*60*60;
		$maximumSecondsDifference = $this->settings['maximumWeeks'] * 7 * $oneDay;
		if (($data['latestBookingTime'] - $data['earliestBookingTime']) > $maximumSecondsDifference) {
			$data['latestBookingTime'] = $data['earliestBookingTime'] + $maximumSecondsDifference;
		}
		
		# Get the Monday in the week of the earliest booking
		$earliestBookingDate = $this->mondayOfDate ($data['earliestBookingTime'], true);
		
		# Similarly, get the Friday in week of latest booking
		$latestBookingDate  = $this->fridayOfDate ($data['latestBookingTime'], true);
		
		# Get the weekdays for this range
		$query = "
			SELECT
				`date`,
				UNIX_TIMESTAMP(`date`) AS timestamp
			FROM {$this->settings['database']}.seededDates
			WHERE
				    `date` >= '{$earliestBookingDate}'
				AND `date` <= '{$latestBookingDate}'
				" . ($this->settings['showWeekends'] ? '' : "AND DAYOFWEEK(`date`) NOT IN (1,7)") . "
		;";
		$weekdaysIso = $this->databaseConnection->getData ($query);
		
		# Regroup by the Monday of the week
		$datesOfWeeks = array ();
		foreach ($weekdaysIso as $dayIso) {
			$mondayTimestamp = timedate::startOfWeek (date ('Y', $dayIso['timestamp']), date ('W', $dayIso['timestamp']));
			$mondayDate = date ('Y-m-d', $mondayTimestamp);
			$datesOfWeeks[$mondayDate][] = $dayIso['date'];
		}
		
		# Return the result
		return $datesOfWeeks;
	}
	
	
/*
	# Function to determine whether edge chopping is needed for when showing custom week(s)
	private function customWeekEdgeChopping ($where)
	{
		# If the custom week starts on the same day as a natural week, then no chopping is required
		if ($this->settings['startingDayNaturalWeek'] == $this->settings['startingDayCustomWeek']) {return false;}
		
		# Look out for date_custom* entries
		foreach ($where as $key => $value) {
			if (preg_match ('/^date_custom/', $key)) {
				return $this->settings['startingDayCustomWeek'];
			}
		}
		
		# Not found, so return false
		return false;
	}
*/
	
	
	# Function to create an HTML grid of days
	private function weekGrid ($startOfWeekIso, $bookingsByDay, $linkableParameters, $highlightBookings, $format, $customWeekStartDates)
	{
		# Start the HTML
		$html = '';
		
		# Start with the heading
		$html .= $this->weekHeading ($startOfWeekIso);
		
		# Start a week
		$html .= "\n\t" . '<div class="week">';
		
		# Define the available width for each row that excludes the title
		$fullWidth = 1;		// i.e. 100%
		$titleSpace = 0.1;	// i.e. 10%
		$availableWidth = $fullWidth - $titleSpace;
		
		# If there are linkable parameters, compile as a query string element
		$linkableParameters = ($linkableParameters ? '&amp;' . http_build_query ($linkableParameters) : '');
		
		# Compile the background timeslot grid, with only the date parameter to be filled in afterwards
		$totalTimeslots = $this->settings['workingDayUntilHour'] - $this->settings['workingDayStartHour'];
		$widthFractionPerTimeslot = ($availableWidth / $totalTimeslots);
		$widthPercentagePerTimeslot = ($widthFractionPerTimeslot * 100);
		$timeslots = array ();
		for ($hour = $this->settings['workingDayStartHour']; $hour < $this->settings['workingDayUntilHour']; $hour++) {
			$hoursLeft = $this->settings['workingDayUntilHour'] - $hour;
			$marginLeftPercentage = (($widthFractionPerTimeslot * $hoursLeft) * 100);
			$hourFormatted = date ('ga', mktime ($hour, 1, 1, 7, 26, 2012));	// Date is arbitrary - all we want is an hour like (int) 8 to become a formatted string like '8am'
			$untilHour = ($hour == 24 ? 0 : $hour) + $this->settings['defaultBookingLengthHours'];
			$link = ($this->userIsEditor ? " <a href=\"{$this->baseUrl}/bookings/add.html?date=%hour&amp;startTime=" . str_pad ($hour, 2, '0', STR_PAD_LEFT) . ":00:00&amp;untilTime=" . str_pad ($untilHour, 2, '0', STR_PAD_LEFT) . ":00:00{$linkableParameters}\" title=\"Create booking here\">+</a>" : '');
			$timeslots[$hour] = sprintf ('<li style="margin-left: -%s%%; width: %s%%;"><div>%s%s</div></li>', $marginLeftPercentage, $widthPercentagePerTimeslot, $hourFormatted, $link);
		}
		$timeslotsGrid = "\n\t\t" . '<ul class="timeslots">' . "\n\t\t\t" . implode ("\n\t\t\t", $timeslots) . "\n\t\t" . '</ul>';
		
		# Get the special dates
		$specialDates = $this->getSpecialDates ();
		
		# Determine today
		$todayIso = date ('Y-m-d');
		
		# Loop through each day
		$css = array ();
		$dayNumber = 0;
		foreach ($bookingsByDay as $dateIso => $bookings) {
			
			# Skip if a previous day if required
			$isPast = ($dateIso < $todayIso);
			if ($this->settings['skipPreviousDays']) {
				if ($isPast) {
					continue;
				}
			}
			
			# Determine the Unix timestamp for midnight at the start of this day
			list ($year, $month, $day) = explode ('-', $dateIso);
			$midnightAtDayStart = mktime (0, 0, 0, $month, $day, $year);
			
			# Determine the day link
			$monthTrimmed = (int) $month;
			$dayTrimmed = (int) $day;
			$dayLink = $this->baseUrl . "/{$year}/{$this->months[$monthTrimmed]}/{$dayTrimmed}/";
			
			# Determine 8am and 6pm
			$workingDayStart = $midnightAtDayStart + ($this->settings['workingDayStartHour']  * 60*60);
			$workingDayEnd = $midnightAtDayStart + ($this->settings['workingDayUntilHour'] * 60*60);
			$lengthOfDay = $workingDayEnd - $workingDayStart;
			
			# Calculate the positioning for each booking
			$marginTopRow = false;
			$idsThisStartTime = array ();
			foreach ($bookings as $id => $booking) {
				
				# Determine the left point
				$startPointFromRight = (($workingDayEnd - $booking['startTime']) / $lengthOfDay);
				$startPointFromRightFraction = $startPointFromRight * $availableWidth;
				$marginLeftPercent = ($startPointFromRightFraction * 100);
				
				# Determine the width
				$fractionOfWorkingDay = (($booking['untilTime'] - $booking['startTime']) / $lengthOfDay);
				$workingDayWidthFraction = $fractionOfWorkingDay * $availableWidth;
				$widthPercent = ($workingDayWidthFraction * 100);
				
				# Add in the computed margin and width
				$bookings[$id]['margin-left'] = round ($marginLeftPercent, 1);
				$bookings[$id]['width'] = round ($widthPercent, 1);
				
				# If the booking is longer than a full day, apply a negative margin-right to the booking, so that the containing CSS box does not get expanded (which would cause other bookings that day to be misaligned with the grid); see: http://stackoverflow.com/questions/628500/
				$bookings[$id]['margin-right'] = NULL;
				if ($fractionOfWorkingDay > 1) {
					$bookings[$id]['margin-right'] = (-1 * ($bookings[$id]['width'] - $bookings[$id]['margin-left'])) . '%';
				}
				
				# Add margin if the start times overlap
				$startTime = $booking['startTime'];
				$idsThisStartTime[$startTime][] = $id;
				$totalThisStartTime = count ($idsThisStartTime[$startTime]);
				$marginTop = (($totalThisStartTime - 1) * $this->settings['overlapPx']);	// Do not add an overlap for the first item
				$bookings[$id]['margin-top'] = $marginTop;
				
				# In grid mode, if there are too many overlaps for this time period, replace the excess ones with a single last one (top of the stack) stating to view the current full day
				if ($format == 'grid') {
					if ($totalThisStartTime > $this->settings['maxVisibleListingsPerTimeslotWhenOverlapping']) {
						$bookingIdHavingMessage = $idsThisStartTime[$startTime][($this->settings['maxVisibleListingsPerTimeslotWhenOverlapping'] - 1)];	// -1 because the list starts at 0
						$excess = $totalThisStartTime - $this->settings['maxVisibleListingsPerTimeslotWhenOverlapping'] + 1;
						$bookings[$bookingIdHavingMessage]['name'] = "+ {$excess} MORE BOOKINGS&hellip;";
						$bookings[$bookingIdHavingMessage]['overlapMessage'] = $dayLink;
						if ($highlightBookings && in_array ($id, $highlightBookings)) {
							$highlightBookings[] = $bookingIdHavingMessage;		// If the ID is to be highlighted, write in its proxy to the highlight list
						}
						unset ($bookings[$id]);
						continue;
					}
				}
				
				# Save the largest margin top
				if ($marginTop > $marginTopRow) {$marginTopRow = $marginTop;}
			}
			
			# Create an HTML list of each booking
			$list = array ();
			foreach ($bookings as $id => $booking) {
				$contents = $this->bookingCellText ($booking);
				$tooltip = $this->bookingCellText ($booking, true);
				$classes = array ();
				if ($highlightBookings && in_array ($id, $highlightBookings)) {$classes[] = 'highlighted';}
				if ($this->userIsEditor && $booking['draft']) {$classes[] = 'draft';}
				$style = ($format == 'grid' ? " style=\"margin-left: -{$booking['margin-left']}%; width: {$booking['width']}%;" . ($booking['margin-right'] ? " margin-right: {$booking['margin-right']};" : '') . ($booking['margin-top'] ? " margin-top: {$booking['margin-top']}px;" : '') . '"' : '');
				$list[$id]  = '<li' . $style . ($classes ? ' class="' . (implode (' ', $classes)) . '"' : '') . ">\n\t\t\t\t<div title=\"{$tooltip}\">{$contents}\n\t\t\t\t</div>\n\t\t\t</li>";
				$startTimePreviousBooking = $booking['startTime'];
			}
			$listHtml = '<ul class="bookings">' . "\n\t\t\t" . implode ("\n\t\t\t", $list) . "\n\t\t" . '</ul>';
			
			# Determine if this is today
			$isToday = ($dateIso == $todayIso);
			
			# Determine if this is a special date
			$isSpecialDate = (isSet ($specialDates[$dateIso]) ? $specialDates[$dateIso]['description'] : false);
			
			# Determine if this day is the start of a custom week
			$customWeekIndicator = (isSet ($customWeekStartDates[$dateIso]) ? 'Start of ' . str_replace ('(', '<br />(', htmlspecialchars ($customWeekStartDates[$dateIso])) : false);
			
			# Add this day to the week chart
			$columnHeightStyle = ($marginTopRow ? ' style="height: ' . ($this->settings['dayHeightPx'] + $marginTopRow) . 'px;"' : '');	// If the height is not the default, extend it, taking account of the IE marging bug also
			$html .= "\n\n\t" . '<div class="day' . ($isPast ? ' past' : '') . ($isToday ? ' today' : '') . ($isSpecialDate ? ' specialdate' : '') . '"' . ($format == 'grid' ? $columnHeightStyle : '') . '>';
			if ($format == 'grid') {
				$html .= str_replace ('date=%hour', 'date=' . $dateIso, $timeslotsGrid);
			}
			$dateAsTime = strtotime ($dateIso);
			$html .= "\n\t\t" . "<h4><div><a href=\"{$dayLink}\" title=\"" . date ('l, jS F', $dateAsTime) . '">' . nl2br (date (($format == 'grid' ? "l\njS F" : 'l, jS F'), $dateAsTime)) . '</a>' . ($isSpecialDate ? '<br /><br /><span>' . htmlspecialchars ($isSpecialDate) . '</span>' : '') . ($customWeekIndicator ? '<br /><br /><span class="customweekindicator">' . $customWeekIndicator . '</span>' : '') . '</div></h4>';
			$html .= "\n\t\t" . $listHtml;
			$html .= "\n\t" . '</div>';
		}
		
		# End the week
		$html .= "\n\t" . '</div>';
		
		# Return the HTML
		return $html;
	}
	
	
	# Function to create the contents of a booking cell
	private function bookingCellText ($booking, $tooltipVersion = false)
	{
		// application::dumpData ($booking);
		
		# Determine the link; if it is an overlap message use the day link
		$isOverlapMessage = (isSet ($booking['overlapMessage']));
		$link = ($isOverlapMessage ? $booking['overlapMessage'] : $this->baseUrl . '/bookings/' . $booking['id'] . '/');
		
		# Start an array of information
		#!# The ordering below should ideally be kept in sync with the booking form order for consistency
		$lines = array ();
		
		# Start with the event title, as a link
		$name = ($tooltipVersion ? strtoupper ($booking['name']) : $booking['name']);
		$name = ($isOverlapMessage ? $booking['name'] : htmlspecialchars ($name));	// For the overlap version, do not use the upper-cased version, as that will have had HTML entity text wrongly upper-cased
		$name .= ($booking['activityNamePrefix'] ? ' (' . $booking['activityNamePrefix'] . ')' : '');
		$editLink = ($this->userIsEditor ? "<a href=\"{$link}edit.html\"><img src=\"/images/icons/pencil.png\" alt=\"\" title=\"Edit this booking\" /></a> " : '');
		$cloneLink = ($this->userIsEditor ? "<a href=\"{$link}clone.html\"><img src=\"/images/icons/page_copy.png\" alt=\"\" title=\"Clone this booking\" /></a> " : '');
		$webLink = ($booking['url'] ? "<a href=\"" . htmlspecialchars ($booking['url']) . "\" target=\"_blank\" class=\"noarrow\"><img src=\"/images/icons/world.png\" alt=\"\" title=\"Go to webpage for this booking\" /></a> " : '');
		$lines['name'] = sprintf ('<h5' . ($isOverlapMessage ? ' class="overlap"' : '') . '>%s<a href="%s">%s</a></h5>', $editLink . $cloneLink . $webLink, $link, $name);
		
		# Add the activity
		$lines[] = "<p class=\"activity\"><a href=\"{$this->baseUrl}/activities/" . htmlspecialchars (urlencode ($booking['activityMoniker'])) . '/">' . htmlspecialchars ($booking['activityName']) . '</a></p>';
		
		# Add the person(s) involved
		$lines[] = '<p class="name">' . $booking['bookedForUserid'] . '</p>';	// The value is in HTML, with links
		
		# Add the location
		$location = $booking['roomName'] . (strlen ($booking['buildingName']) ? ', ' . $booking['buildingName'] : '');
		$lines[] = "<p class=\"location\"><a href=\"{$this->baseUrl}/rooms/" . htmlspecialchars (urlencode ($booking['roomMoniker'])) . '/">' . ($location ? htmlspecialchars ($location) : 'Location: ?') . '</a></p>';
		
		# Add the title
		$lines[] = sprintf ('<p class="time">%s-%s%s</p>', $booking['startTimeFormatted'], $booking['untilTimeFormatted'], ($tooltipVersion ? ', ' . date ('jS F Y', strtotime ($booking['startDate'])) : ''));
		
		# Show who booked it, and when, for the tooltip version only
		if ($tooltipVersion) {
			$lines['bookedby'] = '<p class="bookedby">Booked by: ' . htmlspecialchars ($booking['bookedByUseridFormatted']) . ' on ' . date ('jS F, Y', $booking['createdAt']) . (($booking['updatedByUseridFormatted'] && ($booking['updatedByUseridFormatted'] != $booking['bookedByUseridFormatted'])) ? '; updated by: ' . htmlspecialchars ($booking['updatedByUseridFormatted']) : '') . '</p>';
		}
		
		# Add notes
		if ($booking['notes']) {
			$lines[] = "\n" . '<p class="notes">Notes: ' . "\n" . str_replace ("\r", '', $booking['notes']) . '</p>';
		}
		
		# If there is an overlap message, take only the title
		if (isSet ($booking['overlapMessage'])) {
			$lines = array ($lines['name']);
			$lines[] = '<p><strong>More bookings</strong> exist for this timeslot.</p>';
			$lines[] = "<p><a href=\"{$link}\">View the full list for this day&hellip;</a></p>";
		}
		
		# Compile the HTML
		$contents = "\n\t\t\t\t\t". implode ("\n\t\t\t\t\t", $lines);
		
		# For the tooltip version, convert to HTML with lines starting without tabs
		if ($tooltipVersion) {
			$contents = strip_tags (str_replace ("\t", '', trim ($contents)));	// Hover text so that truncated entries can still have their text available
			$contents = str_replace ("\n", '&#10;', trim ($contents));	// Use &#10; as line break rather than natural linebreaks: http://stackoverflow.com/questions/6502054
		}
		
		# Return the HTML
		return $contents;
	}
	
	
	# Function to show a day listing
	public function dayGrid ($bookings, $todayIso)
	{
		# Start the HTML
		$html = '';
		
		# Give a link to add a booking
		if ($this->userIsEditor) {
			$html  = "\n<br />";
			$html .= "\n<p>You can <a class=\"actions\" href=\"{$this->baseUrl}/bookings/add.html?date={$todayIso}\"><img src=\"/images/icons/add.png\" alt=\"\" class=\"icon\" /> Make a booking for this day</a></p>";
		}
		
		# End if no bookings
		if (!$bookings) {
			#!# Fix styling properly
			$html .= "\n<p>There are <strong>no bookings so far</strong> on this day.</p>";
			return $html;
		}
		
		# Show the bookings
		
		#!# Temporary implementation
		$html .= "\n<p class=\"warning\"><strong>This listing not yet fully formatted.</strong><br />It will show the following data as a listing, by hour, showing the rooms and people.</p>";
		
		# Remove unwanted headings
		foreach ($bookings as $id => $booking) {
			unset ($bookings[$id]['startTime']);
			unset ($bookings[$id]['untilTime']);
		}
		
		# Create the table
		$headings = $this->databaseConnection->getHeadings ($this->settings['database'], 'bookings');
		$html .= "\n" . '<!-- Enable table sortability: --><script language="javascript" type="text/javascript" src="/sitetech/sorttable.js"></script>';
		$html .= application::htmlTable ($bookings, $headings, 'lines sortable" id="sortable', $keyAsFirstColumn = false, false, $allowHtml = true, $showColons = false, $addCellClasses = false, $addRowKeyClasses = false, $onlyFields = array (), $compress = false, $showHeadings = true, $encodeEmailAddress = true);
		
		# Return the HTML
		return $html;
	}
	
	
	# 'More' page (basically just a link to other subtabs
	public function more ()
	{
		# Create the HTML
		$html  = "\n<p>This section contains various functions available to editors only.</p>";
		
		# Get the main object types
		$mainObjectActions = array ();
		foreach ($this->actions as $action => $attributes) {
			if (isSet ($attributes['crudViewAsTimetable'])) {
				$mainObjectActions[$action] = $attributes;
			}
		}
		
		# Get the other objects (those under 'More')
		$otherObjectActions = $this->getChildActions (__FUNCTION__, false, false);
		
		# Combine
		$actions = array_merge ($mainObjectActions, $otherObjectActions);
		
		# Split out standalone pages
		$standalonePages = array ('today');
		$otherPages = application::array_filter_keys ($actions, $standalonePages);
		foreach ($standalonePages as $standalonePage) {
			unset ($actions[$standalonePage]);
		}
		
		# Compile the HTML, adding a heading
		$html  = "\n<p>You can edit these kinds of items:</p>";
		$html .= $this->actionsListHtml ($actions, true, 'boxylist objectlist');
		
		# Other pages
		$html .= "\n<p>Other pages:</p>";
		$html .= $this->actionsListHtml ($otherPages, true, 'boxylist objectlist');
		
		
		# Show the HTML
		echo $html;
	}
	
	
	
	/* ---------------------------- */
	/*        Section functions     */
	/* ---------------------------- */
	
	
	# Bookings section
	public function bookings ()
	{
		# Get the activities hierarchy
		if (!$this->activitiesHierarchy = $this->getActivities (false, false, true, true, $errorHtml)) {
			echo $errorHtml;
			return false;
		}
		
		# Get the rooms, as multidimensional listing, and attach any notes
		$rooms = $this->getRooms (false, $includeNote = true, $splitByInternalExternal = false, $indexById = true);
		
		# Get the actions, action and ID
		if (!$actionsActionId = $this->getActionsActionId ()) {return false;}
		list ($actions, $action, $id, $linkId) = $actionsActionId;
		
		# Define dataBinding overrides
		$dataBindingParameters = array (
			'exclude' => array_merge (array ('id', 'series'), $this->hideFields, ($action == 'search' ? array () : array ('bookedByUserid', 'updatedByUserid'))),
			'attributes' => array (
				'name' => array ('heading' => array (3 => 'Main details'), 'size' => 50, ),
				'areaOfActivityId' => array ('values' => hierarchy::asIndentedListing ($this->activitiesHierarchy), 'description' => 'If a suitable area of activity is not listed yet, click on &hellip; to add it'),
				// 'eventTypeId' => array ('copyTo' => 'name', ),
				'bookedForUserid' => $this->expandablePeopleFieldSpec (),
				'roomId' => array ('values' => $rooms, ),
				#!# Date bug that /12 as year becomes 0012
				'date' => array_merge (array ('heading' => array (3 => 'Dates and times'), ), $this->datePickerAttributes ()),
				'url' => array ('heading' => array (3 => 'Other details'), 'size' => 60, 'placeholder' => 'https://...', 'description' => false, ),
				'notes' => array ('rows' => 2, 'cols' => 40, ),
				'hideFromDisplayBoard' => array ('title' => "Hide from <a href=\"{$this->baseUrl}/today/\" target=\"_blank\" title=\"[Link opens in a new window]\">display board listing</a>?"),
				'bookedByUserid'  => ($this->settings['usersAutocomplete'] ? array ('autocomplete' => $this->settings['usersAutocomplete'], 'autocompleteOptions' => array ('delay' => 0), 'description' => 'Type a surname or username; one person per line only', ) : array ()),	// Will only take effect when visible, i.e. on the search page
				'updatedByUserid' => ($this->settings['usersAutocomplete'] ? array ('autocomplete' => $this->settings['usersAutocomplete'], 'autocompleteOptions' => array ('delay' => 0), 'description' => 'Type a surname or username; one person per line only', ) : array ()),	// Will only take effect when visible, i.e. on the search page
				
			),
		);
		
		# Permit defaults from a query string
		#!# ultimateForm really needs a native way of doing this, by supplying such a whitelist
		if ($action == 'add') {
			$permittedUrlArguments = array ('date', 'startTime', 'untilTime', 'areaOfActivityId', 'bookedForUserid', 'roomId', );
			foreach ($permittedUrlArguments as $permittedUrlArgument) {
				if (isSet ($_GET[$permittedUrlArgument])) {
					$dataBindingParameters['attributes'][$permittedUrlArgument]['default'] = $_GET[$permittedUrlArgument];
				}
			}
		}
		
		# Define fixed data
		$fixedData = array ();
		switch ($action) {
			case 'add':
			case 'clone':
				$fixedData['bookedByUserid'] = $this->user;
				break;
			case 'edit':
				#!# Ideally this would only be added if the user is not the same as the booked-by user, but there is no useful place to hook into that phase (e.g. at this phase, we don't actually have the data so can't determine this)
				$fixedData['updatedByUserid'] = $this->user;
				break;
			default:
		}
		
		#!# The table names should be consistent and/or should be defined in the actions list
		echo $this->crudEditing ($dataBindingParameters, $fixedData);
	}
	
	
	# Function to provide standard date picker attributes
	private function datePickerAttributes ()
	{
		# Return the details
		return array (
			'picker' => true,
			'min' => (date ('Y') - $this->settings['yearsBehindBookable']) . date ('-m-d'),
			'max' => (date ('Y') + $this->settings['yearsAheadBookable'])  . date ('-m-d'),
		);
	}
	
	
	/*
	# Function to deal with series management
	private function getSeriesTypes ()
	{
		# Start an array of series types
		$seriesTypes = array ();
		
		# Several weeks
		$seriesTypes['weeks1'] = 'and the following 1 week';
		$seriesTypes['weeks2'] = 'and the following 2 weeks';
		$seriesTypes['weeks3'] = 'and the following 3 weeks';
		$seriesTypes['weeks4'] = 'and the following 4 weeks';
		$seriesTypes['weeks5'] = 'and the following 5 weeks';
		$seriesTypes['weeks6'] = 'and the following 6 weeks';
		$seriesTypes['weeks7'] = 'and the following 7 weeks';
		$seriesTypes['weeks8'] = 'and the following 8 weeks';
		
		# Custom year
		// if ($this->settings['customYearLabel']) {$seriesTypes['customYear'] = 'Rest of this ' . $this->settings['customYearLabel'];}
		
		# All days in a week
		$seriesTypes['weeks4'] = 'and the following 4 week';
		
		# Return the array
		return $seriesTypes;
	}
	*/
	
	
	# Function to view a booking, in the form of its position in the timetable
	private function crudEditingViewBookings ($table, $id, $dataBindingParameters, $fixedData)
	{
		# Force the user to the current booking
		$forcedParameters = array ('object' => 'bookings');
		
		# Get the HTML from the browsing listing
		$html = $this->browsingListing ($forcedParameters, $id);
		
		# Return the HTML
		return $html;
	}
	
	
	# Function to view draft bookings
	private function crudEditingRequestBookings ($table, $id, $dataBindingParameters, $fixedData)
	{
		# Start the HTML
		$html = '';
		
		$html = $this->crudEditingAdd ($table, $id, $dataBindingParameters, $fixedData, false, $requestMode = true);
		
		# Return the HTML
		return $html;
	}
	
	
	# Function to view draft bookings
	private function crudEditingDraftBookings ($table, $id, $dataBindingParameters, $fixedData, $limitToUser = true)
	{
		# Start the HTML
		$html = '';
		
		# Get all the draft bookings, limited to the user who created them
		$conditions = array ('draft' => '1');
		if ($limitToUser) {
			$conditions['bookedByUserid'] = $this->user;
		}
		$data = $this->databaseConnection->select ($this->settings['database'], 'bookings', $conditions);
		
		# End if none
		if (!$data) {
			$html = "\n<p>" . ($limitToUser ? 'You have not created any' : 'There are no') . ' bookings that are currently in draft.</p>';
			return $html;
		}
		
		# Create a data listing table to use as the template, with placeholders for editable checkboxes
		$checkboxes = array ();
		foreach ($data as $key => $booking) {
			$checkboxes[$key] = 'Yes';
			$checkboxColumn = array ('approve' => '{approve_' . $key . '}');
			$data[$key] = array_merge ($checkboxColumn, $booking);
		}
		$label = 'Make live?';
		$additionalHeadings = array ('approve' => $label);
		$template = $this->dataListing ($data, $table, $additionalHeadings);
		
		# Create the form
		$form = new form (array (
			'display' => 'template',
			'displayTemplate' => '{[[PROBLEMS]]}' . $template . '<p>{[[SUBMIT]]}</p>',
			'formCompleteText' => false,
			'div' => 'draftslist',
		));
		$form->checkboxes (array (
			'name'		=> 'approve',
			'title'		=> $label,
			'values'	=> $checkboxes,
			'default'	=> array_keys ($checkboxes),
			'output'	=> array ('processing' => 'special-setdatatype'),
		));
		if (!$result = $form->process ($html)) {
			return $html;
		}
		
		# Update the data
		$query = "UPDATE {$this->settings['database']}.bookings SET draft = NULL WHERE id IN({$result['approve']});";
		$databaseResult = $this->databaseConnection->query ($query);
		
		# Confirm the result
		$html .= $this->flashMessage ($result['approve'], 'edit');	// $result['approve'] do not need moniker conversion since bookings are numeric
		
		# Return the HTML
		return $html;
	}
	
	
	# Function to view draft bookings
	private function crudEditingAlldraftsBookings ($table, $id, $dataBindingParameters, $fixedData)
	{
		return $this->crudEditingDraftBookings ($table, $id, $dataBindingParameters, $fixedData, $limitToUser = false);
	}
	
	
	# Rooms
	public function rooms ()
	{
		# Define dataBinding overrides
		$dataBindingParameters = array (
			'attributes' => array (
				'moniker' => array ('regexp' => '^([-0-9a-z]{1,40})$', 'prepend' => $this->baseUrl . '/' . __FUNCTION__ . '/', 'append' => '/', 'size' => 20, ),
			),
		);
		
		echo $this->crudEditing ($dataBindingParameters);
	}
	
	
	# Activities
	public function activities ()
	{
		# Determine whether to hide from new
		#!# Currently this would prevent editing of a node under a hidden node
		if (!$actionsActionId = $this->getActionsActionId ()) {return false;}
		list ($actions, $action, $id, $linkId) = $actionsActionId;
		$hideFromNew = ($action != 'list');
		
		# Get the activities hierarchy
		if (!$this->activitiesHierarchy = $this->getActivities (false, false, $hideFromNew, true, $errorHtml)) {
			echo $errorHtml;
			return false;
		}
		
		# Define dataBinding overrides
		$dataBindingParameters = array (
			'int1ToCheckbox' => 'Hide',
			'attributes' => array (
				'parentId' => array ('values' => hierarchy::asIndentedListing ($this->activitiesHierarchy), ),
				'people' => $this->expandablePeopleFieldSpec (),
				'moniker' => array ('regexp' => '^([-0-9a-z]{1,40})$', 'prepend' => $this->baseUrl . '/' . __FUNCTION__ . '/', 'append' => '/', 'size' => 20, ),
				'shortname' => array ('description' => 'This will be added to the title of <strong>all</strong> bookings below this level of activity', ),
			),
		);
		
		# Hand over to CRUD editing
		echo $this->crudEditing ($dataBindingParameters);
	}
	
	
	# Function to define a standard expandable people field
	private function expandablePeopleFieldSpec ()
	{
		# Return the options
		$options = array (
			'type' => 'select',
			'multiple' => true,
			'expandable' => true,
			'separator' => '|',
			'separatorSurround' => true,
			'defaultPresplit' => true,
			'autocomplete' => $this->settings['usersAutocomplete'],
			'autocompleteOptions' => array ('delay' => 0),
			'output' => array ('processing' => 'compiled'),
			'description' => 'Type a surname or username; one person per line only',
		);
		
		return $options;
	}
	
	
	public function crudEditingAddActivities ($table, $id, $dataBindingParameters)
	{
		# Start the HTML
		$html = '';
		
		# If a supplied ID is empty or not numeric, throw a 404
		if (isSet ($_GET['parent']) && (!strlen ($_GET['parent']) || !ctype_digit ($_GET['parent']))) {
			$html = "\n<p>The supplied ID (<em>" . htmlspecialchars ($_GET['parent']) . "</em>) was not correct. Please check the URL and try again.</p>";
			return $html;
		}
		
		# Start with no parent ID supplied
		$parentId = false;
		
		# If a parent ID (whose syntax is already confirmed correct) is specified, require selection
		if (isSet ($_GET['parent'])) {
			$parentId = $_GET['parent'];
			
			# Ensure the node exists
			if (!$node = $this->hierarchy->nodeExists ($parentId)) {
				$html = "\n<p>The supplied ID (<em>" . htmlspecialchars ($_GET['parent']) . "</em>) does not exist.</p>";
				$parentId = false;
				return $html;
			}
		}
		
		# Get the children of this node
		$children = $this->hierarchy->childrenOf ($parentId, "{$this->baseUrl}/activities/add.html?parent=%s");
		
		# If selection is required, give a list of current nodes
		/*
		if ($children) {
			$html .= "\n<p>Please firstly select the area of activity that this comes under:</p>";
			$html .= application::htmlUl ($children);
			return $html;
		}
		*/
		
		# Add additional dataBinding overrides
		$dataBindingParameters['attributes']['parentId']['default'] = $parentId;
		$dataBindingParameters['attributes']['parentId']['editable'] = true;
		
		# Add the edit form
		$html = $this->crudEditingAdd ($table, $id, $dataBindingParameters);
		
		# Return the HTML
		return $html;
	}
	
	
	# Event types
	public function eventtypes ()
	{
		echo $this->crudEditing ();
	}
	
	
	# Editors
	public function editors ()
	{
		# Define dataBinding overrides
		$dataBindingParameters = array (
			'attributes' => array (
				'userid' => ($this->settings['usersAutocomplete'] ? array ('autocomplete' => $this->settings['usersAutocomplete'], 'autocompleteOptions' => array ('delay' => 0), ) : array ()),
			),
		);
		
		# Only admins can change reviewer attribute
		if ($this->userIsAdministrator ()) {
			$dataBindingParameters['exclude'][] = 'reviewer';
		}
		
		echo $this->crudEditing ($dataBindingParameters);
	}
	
	
	# Buildings
	public function buildings ()
	{
		echo $this->crudEditing ();
	}
	
	
	# Terms
	public function terms ()
	{
		# Start the HTML
		$html = '';
		
		# Obtain any prefilled data from the URL
		#!# startYear= and endYear= are permitted in the URL - need to whitelist these when using add
		
		# Define dataBinding overrides
		$dataBindingParameters = array (
			'attributes' => array (
				'termLabel' => array ('type' => 'select', 'values' => $this->getTermLabels (), ),
			),
		);
		
		# Provide a reference link if required
		if ($this->settings['termDatesUrl']) {
			$html .= "\n<p>This <a href=\"{$this->settings['termDatesUrl']}\" target=\"_blank\" title=\"[Link opens in a new window]\">list of term dates</a> may be useful for reference when updating this list.</p>";
		}
		
		$html .= $this->crudEditing ($dataBindingParameters);
		
		# Show the HTML
		echo $html;
	}
	
	
	# Special dates
	public function specialdates ()
	{
		echo $this->crudEditing ();
	}
	
	
	# Today
	public function today ()
	{
		# Start the HTML
		$html = "\n<h1>Timetable - " . date ('jS F') . '</h1>';
		
		# Set to today only
		$where = array ();
		$where['date_implicit_from'] = "`date` = '" . date ('Y-m-d') . "'";
		
		# Hide hidden bookings
		$where['hide'] = "(hideFromDisplayBoard IS NULL or hideFromDisplayBoard = '')";
		
		# Get the bookings
		$bookings = $this->getBookings ($where);
		
		# Filter bookings that have already passed
		$bookingAtStartOfDay = count ($bookings);
		foreach ($bookings as $id => $booking) {
			if (time () > $booking['untilTime']) {
				unset ($bookings[$id]);
			}
		}
		
		# Show the listing
		if (!$bookings) {
			if ($bookingAtStartOfDay) {
				$html .= "\n<p>All lectures, meetings or events for today have now finished.</p>";
			} else {
				$html .= "\n<p>There are no lectures, meetings or events planned today.</p>";
			}
		} else {
			
			# Add each booking
			$html .= "\n<dl>";
			foreach ($bookings as $booking) {
				$html .= "\n\t<dt>";
				$html .= $booking['startTimeFormatted'] . '-' . $booking['untilTimeFormatted'] . ': ' . htmlspecialchars ($booking['name']) . ($booking['activityNamePrefix'] ? ' (' . $booking['activityNamePrefix'] . ')' : '');
				$html .= "</dt>";
				$html .= "\n\t\t<dd>";
				$html .= htmlspecialchars ($booking['roomName'] . (strlen ($booking['buildingName']) ? ', ' . $booking['buildingName'] : ''));
				$html .= "</dd>";
			}
			$html .= "\n</dl>";
		}
		
		# Surround with a div
		$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html lang="en">
		<head>
			<title>Today\'s timetable</title>
			<style type="text/css" media="all">
				body {margin: 0; padding: 20px;}
				body {font-size: 82%;}
				body, input, textarea, select {font-family: arial, helvetica, sans-serif; color: #333;}
				h1, h2, h3, h4, h5 {font-family: arial, helvetica, sans-serif; color: #603;}
				h1 {font-size: 3.4em; width: 97%; background-color: purple; color: white; margin: 0 0 0.5em; padding: 10px;}
				h2 {font-size: 1.5em; font-family: verdana, arial, helvetica, sans-serif; border-bottom: 0; font-weight: bold; padding-bottom: 5px; margin-bottom: 0; margin-top: 15px; background-color: #eee; padding: 5px;}
				h3 {border-bottom: 0; font-size: 1.7em; font-weight: bold; padding-bottom: 5px; margin-bottom: 0; margin-top: 50px; background-color: #eee; padding: 5px;}
				p, dl {font-size: 2.4em;}
				p {line-height: 1.45em; margin: 12px 0 12px 30px;}
				dt {font-weight: bold; color: #600; margin-top: 1.5em;}
				dd {margin-top: 0.4em;}
				
			</style>
			<meta http-equiv="refresh" content="30">
		</head>
		
		<body>
		
		' . $html . '
		
		</body>
		</html>
		';
		
		# Show the HTML
		echo $html;
	}
	
	
	# People
	public function people ()
	{
		# Define dataBinding overrides
		$dataBindingParameters = array (
			'attributes' => array (
				'id'  => ($this->settings['usersAutocomplete'] ? array ('autocomplete' => $this->settings['usersAutocomplete'], 'autocompleteOptions' => array ('delay' => 0), 'description' => 'Type a surname or username; one person per line only', ) : array ()),	// Will only take effect when visible, i.e. on the search page
			),
		);
		
		# Hand-off to CRUD editing
		echo $this->crudEditing ($dataBindingParameters);
	}
	
	
	# People editing - disable direct editing
	private function crudEditingAddPeople ($table, $id, $dataBindingParameters, $fixedData)
	{
		return $this->peopleEditability ($table, $id, $dataBindingParameters, $fixedData, 'add');
	}
	
	private function crudEditingEditPeople ($table, $id, $dataBindingParameters, $fixedData)
	{
		return $this->peopleEditability ($table, $id, $dataBindingParameters, $fixedData, 'edit');
	}
	
	private function crudEditingClonePeople ($table, $id, $dataBindingParameters, $fixedData)
	{
		return $this->peopleEditability ($table, $id, $dataBindingParameters, $fixedData, 'clone');
	}
	
	private function crudEditingDeletePeople ($table, $id, $dataBindingParameters, $fixedData)
	{
		return $this->peopleEditability ($table, $id, $dataBindingParameters, $fixedData, 'delete');
	}
	
	private function peopleEditability ($table, $id, $dataBindingParameters, $fixedData, $do)
	{
		# Disable CRUD editing if the data is being auto-populated from an external source
		if ($this->settings['usersExternalDatabase']) {
			$html  = "\n<p>The list of people is coming from an " . ($this->settings['usersExternalUrl'] ? "<a target=\"_blank\" href=\"{$this->settings['usersExternalUrl']}\">external datasource</a>" : 'external datasource') . ", so cannot be edited here in the Timetable system.</p>";
			return $html;
		}
		
		# Otherwise use the standard CRUD management
		$function = 'crudEditing' . $do;
		$html .= $this->{$function} ($table, $id, $dataBindingParameters, $fixedData);
		return $html;
	}
	
	
	
	
	/* ---------------------------- */
	/*        CRUD functions        */
	/* ---------------------------- */
	
	
	# CRUD editing
	private function crudEditing ($dataBindingParameters = array (), $fixedData = array ())
	{
		# Start the HTML
		$html = '';
		
		# Get the actions, action and ID
		if (!$actionsActionId = $this->getActionsActionId ()) {return false;}
		list ($actions, $action, $id, $linkId) = $actionsActionId;
		
		# Determine the table
		$table = (isSet ($this->actions[$this->action]['table']) ? $this->actions[$this->action]['table'] : $this->action);
		
		# Add a manually-created page title if required
		$html .= $this->manualPageTitle ($table, $id);
		
		# Define links, which will only be shown to Editors
		$html .= $this->crudLinks ($actions, $action);
		
		# Display a flash if required
		if ($action == 'view') {
			$html .= $this->flashMessage ($linkId);
		}
		
		# In list/view modes, redirect listings and check access manually
		if (isSet ($this->actions[$this->action]['crudViewAsTimetable'])) {
			
			# If the action requires that the listing view uses the timetable view format, redirect to its custom listing / timetable item view
			#!# Remove this switch and make the functions just be handled in the default way of functionDo(), with those passing through to roomsLinks() etc
			switch ($action) {
				
				case 'list':
					$function = $this->action . 'Links';	// e.g. roomsLinks
					if (!method_exists ($this, $function)) {break;}	// Use the generic function if no specific listing view is defined
					$html .= $this->{$function} ();
					return $html;
					
				case 'view':
					$forcedParameters = array ('object' => $this->action);
					$html .= $this->browsingListing ($forcedParameters, $id);
					return $html;
					
				# For all editing functions, end if the user is not an editor
				default:
					if (!$this->userIsEditor) {
						$html .= ($this->user ? "\n<p>Only Editors can access this area.</p>" : "\n<p>Please firstly log in.</p>");
						return $html;
					}
			}
		}
		
		# Define the generic function to run and a specific action which can be checked for
		$genericFunction = 'crudEditing' . ucfirst ($action);	// e.g. crudEditingAdd
		// $specificFunction = $this->action . ucfirst ($action);	// e.g. widgetsList
		$specificFunction = $genericFunction . ucfirst ($this->action);	// e.g. crudEditingListBookings
		
		# Run the action
		$function = (method_exists ($this, $specificFunction) ? $specificFunction : $genericFunction);
		$html .= $this->{$function} ($table, $id, $dataBindingParameters, $fixedData);
		
		# Return the HTML
		return $html;
	}
	
	
	# Helper function to assign the actions, action and ID for CRUD editing
	private function getActionsActionId ()
	{
		# End if no action or it is empty
		if (!isSet ($_GET['do']) || !strlen ($_GET['do'])) {
			#!# 404 page
			return false;
		}
		
		# Determine if there is an ID
		$linkId = (isSet ($_GET['id']) ? $_GET['id'] : NULL);
		
		# Determine the text of the view tab
		$viewText = ($this->action == 'bookings' ? 'View booking' : (isSet ($this->actions[$this->action]['crudViewAsTimetable']) ? 'View bookings' : 'View'));
		$viewTooltip = ($this->action == 'bookings' ? 'View this booking in context' : (isSet ($this->actions[$this->action]['crudViewAsTimetable']) ? 'View bookings relevant to this ' . (isSet ($this->actions[$this->action]['descriptionSingular']) ? $this->actions[$this->action]['descriptionSingular'] : 'item') : 'View'));
		
		# List available actions
		$linkIdSanitised = (!is_null ($linkId) ? htmlspecialchars (urlencode ($linkId)) : NULL);
		$actions = array (
			''			=> 'Editor functions:&nbsp;',
			'list'		=> "<a title=\"List all items\" href=\"{$this->baseUrl}/{$this->action}/\">" . '<img src="/images/icons/application_view_list.png" alt="" class="icon" /> List all</a>',
			'search'	=> "<a title=\"Search these items\" href=\"{$this->baseUrl}/{$this->action}/search.html\">" . '<img src="/images/icons/magnifier.png" alt="" class="icon" /> Search</a>',
			'add'		=> "<a title=\"Add another item\" href=\"{$this->baseUrl}/{$this->action}/add.html\">" . '<img src="/images/icons/add.png" alt="" class="icon" /> Add</a>',
			'view'		=> ($linkId ? "<a title=\"" . $viewTooltip . "\" href=\"{$this->baseUrl}/{$this->action}/{$linkIdSanitised}/\">" . '<img src="/images/icons/page_white.png" alt="" class="icon" /> ' . $viewText . '</a>' : false),
			'edit'		=> ($linkId ? "<a title=\"Edit this item\" href=\"{$this->baseUrl}/{$this->action}/{$linkIdSanitised}/edit.html\">" . '<img src="/images/icons/page_white_edit.png" alt="" class="icon" /> Edit</a>' : false),
			'clone'		=> ($linkId ? "<a title=\"Make a copy of this item\" href=\"{$this->baseUrl}/{$this->action}/{$linkIdSanitised}/clone.html\">" . '<img src="/images/icons/page_copy.png" alt="" class="icon" /> Duplicate</a>' : false),
			'delete'	=> ($linkId ? "<a title=\"Delete this item\" href=\"{$this->baseUrl}/{$this->action}/{$linkIdSanitised}/delete.html\">" . '<img src="/images/icons/page_white_delete.png" alt="" class="icon" /> Delete</a>' : false),
			'draft'		=> ($this->action == 'bookings' ? "<a title=\"My draft bookings\" href=\"{$this->baseUrl}/{$this->action}/draft.html\">" . '<img src="/images/icons/page_white_dvd.png" alt="" class="icon" /> My draft bookings</a>' : false),
			'alldrafts'	=> ($this->action == 'bookings' ? "<a title=\"Your draft bookings\" href=\"{$this->baseUrl}/{$this->action}/alldrafts.html\">" . '<img src="/images/icons/page_white_dvd.png" alt="" class="icon" /> All draft bookings</a>' : false),
			'request'	=> ($this->action == 'bookings' ? "<a title=\"Request a booking\" href=\"{$this->baseUrl}/{$this->action}/request.html\">" . '<img src="/images/icons/page_white_add.png" alt="" class="icon" /> Booking request</a>' : false),
		);
		
		# Determine the requested action and ensure it is valid
		$action = $_GET['do'];
		if (!isSet ($actions[$action])) {
			#!# 404 page
			return false;
		}
		
		# For tables that use monikers, internally treat the _GET['id'] as the moniker and look up the actual (numeric) ID
		$id = $this->getActualId ($linkId);
		
		# End if no item supplied
		if (!is_null ($linkId)) {
			if (is_null ($id)) {
				#!# 404 perhaps?
				echo "\n<p>No valid item identifier was supplied. Please check the URL and try again.</p>";
				return false;
			}
		}
		
		# Return the values
		return array ($actions, $action, $id, $linkId);
	}
	
	
	# Function to get the URL ID from a supplied moniker value
	private function getActualId ($id)
	{
		# End if none
		if (is_null ($id)) {return $id;}
		
		# Determine if the current object type uses monikers
		$table = (isSet ($this->actions[$this->action]['table']) ? $this->actions[$this->action]['table'] : $this->action);
		$fields = $this->databaseConnection->getFields ($this->settings['database'], $table);
		if (!isSet ($fields['moniker'])) {return $id;}
		
		# Select the item
		if (!$item = $this->databaseConnection->selectOne ($this->settings['database'], $table, array ('moniker' => $id))) {return NULL;}
		
		# Return the ID value
		return $item['id'];
	}
	
	
	# Function to generate a manually-created page title
	private function manualPageTitle ($table, $id)
	{
		# If there is no heading flag defined in the actions list, end
		if (!isSet ($this->actions[$this->action]['heading']) || $this->actions[$this->action]['heading']) {return false;}
		
		# Default the title to the action description
		$title = $this->actions[$this->action]['description'];
		
		# If the ID exists, look up the name for that item
		if ($id) {
			if ($data = $this->databaseConnection->selectOne ($this->settings['database'], $table, array ('id' => $id))) {
				if (isSet ($data['name'])) {
					$title .= ': <span>' . htmlspecialchars ($data['name']) . '</span>';
				}
			}
		}
		
		# Compile the HTML
		$html = "\n<h2>{$title}</h2>";
		
		# Return the HTML
		return $html;
	}
	
	
	# Function to create a set of context-sensitive CRUD links
	private function crudLinks ($actions, $action)
	{
		# Do not show the tabs if the user is not an editor
		if (!$this->userIsEditor) {return false;}
		
		# Start the HTML
		$html = '';
		
		# Add a minisearch form
		$what = strtolower ($this->actions[$this->action]['description']);
		$html .= $this->minisearch ($what);
		
		# Compile the HTML
		$html .= application::htmlUl ($actions, 0, 'crudlist tabsflat', true, false, false, $liClass = true, $action);
		
		# Return the HTML
		return $html;
	}
	
	
	# Listing
	private function crudEditingList ($table, $id)
	{
		# Start the HTML
		$html = '';
		
		# Determine whether to paginate
		$paginate = ($this->action == 'bookings');
		
		# Determine if this is an export type
		#!# Need to merge the 'exportall' system with the main 'export' system
		$supportedFormats = array ('csv');
		$exportFormat = (isSet ($_GET['exportall']) && in_array ($_GET['exportall'], $supportedFormats) ? $_GET['exportall'] : false);
		if ($exportFormat) {
			$paginate = false;
		}
		
		# Add export link
		if (!$exportFormat) {
			if ($table == 'bookings') {		// Currently only supported for bookings
				$html .= "\n<p class=\"right\"><a href=\"{$this->baseUrl}/{$table}/{$table}.csv\">Export all</a></p>";
			}
		}
		
		# Get the data, using paginated listings for bookings
		if ($paginate) {
			$page = (isSet ($_GET['page']) && ctype_digit ($_GET['page']) ? $_GET['page'] : 1);
			$query = "SELECT * FROM {$this->settings['database']}.{$table} ORDER BY id DESC;";
			list ($data, $totalAvailable, $totalPages, $page, $actualMatchesReachedMaximum) = $this->databaseConnection->getDataViaPagination ($query, "{$this->settings['database']}.{$table}", true, array (), array (), $this->settings['paginationRecordsPerPage'], $page);
		} else {
			$data = $this->databaseConnection->select ($this->settings['database'], $table, array (), array (), true, $orderBy = 'id');
		}
		
		# Convert booleans to ticks
		$fields = $this->databaseConnection->getFields ($this->settings['database'], $table);
		$data = application::booleansToTicks ($data, $fields);
		
		# Convert times to be more human-readable
		$data = $this->simplifyTimes ($data);
		
		# Look up the description for this action type
		$description = strtolower ($this->actions[$this->action]['description']);
		
		# End if no data
		if (!$data) {
			$html .= "\n<p>There are no {$description} entries so far. You may wish to <a href=\"{$this->baseUrl}/{$this->action}/add.html\">add one</a>.</p>";
			return $html;
		}
		
		# Introduce the listing
		$html .= "\n<p>Here are all the {$description}" . ($this->action == 'bookings' ? ', newest first' : '') . ':</p>';
		
		# If the data is hierarchical, show a hierarchical display instead of a table listing
		#!# This code seems to be unused - need to check on its status
		if ($this->databaseConnection->isHierarchical ($this->settings['database'], $table)) {
			if ($this->userIsEditor) {
				$html .= "\n<p><strong>Click on [+] to add a new {$description} to that item:</strong></p>";
			}
			$html .= hierarchy::asUl ($this->activitiesHierarchy, "{$this->baseUrl}/activities/", ($this->userIsEditor ? '/add.html?parent=%s' : false), ($this->userIsEditor ? '/%s/edit.html' : ''));
			return $html;
		}
		
		# Show pagination links if required
		if ($paginate) {
			$html .= "\n<p>There " . ($totalAvailable == 1 ? 'is <strong>one</strong> booking.' : 'are <strong>' . number_format ($totalAvailable) . "</strong> bookings.");
			$html .= ($totalPages == 1 ? '' : " Showing {$this->settings['paginationRecordsPerPage']} records per page.") . '</p>';
			require_once ('pagination.php');
			$html .= pagination::paginationLinks ($page, $totalPages, $this->baseUrl . "/{$this->action}/", false, 'paginationlinks', true);
		}
		
		# Create the data listing table
		$html .= $this->dataListing ($data, $table, array (), $exportFormat);
		
		# Return the HTML
		return $html;
	}
	
	
	# Function to convert times to be more human-readable, e.g. '09:00:00' becomes '9am'
	private function simplifyTimes ($data)
	{
		# Convert each field
		require_once ('timedate.php');
		foreach ($data as $index => $record) {
			foreach ($record as $field => $value) {
				if (preg_match ('/time$/i', $field)) {
					$data[$index][$field] = timedate::simplifyTime ($value, true);
				}
			}
		}
		
		# Return the data
		return $data;
	}
	
	
	# Function to create a data listing table/export; must be public so that the multisearch resultRenderer can reach it
	public function dataListing ($data, $table, $additionalHeadings = array (), $exportFormat = false, $editingLinks = true)
	{
		# End if none
		if (!$data) {return '';}
		
		# Remove unwanted fields
		$data = $this->removeUnwantedFields ($data);
		
		# Add in activityNamePrefix
		#!# This is a rather kludgy fix
		if ($exportFormat == 'csv') {
			#!# Convert areaOfActivityId (as used in bookings part of code) to activityId (as used in CRUD mode)
			foreach ($data as $id => $booking) {
				$data[$id]['activityId'] = $data[$id]['areaOfActivityId'];
			}
			$data = $this->substituteAreaOfActivityShortnames ($data, 'areaOfActivityId');
		}
		
		# Substitute values in join fields for their names
		$data = $this->databaseConnection->substituteJoinedData ($data, $this->settings['database'], $table, 'name');
		
		# Substitute in names
		$data = $this->substituteUseridTokensToNames ($data, $this->userFields);
		
		# Get the headings
		$headings = $this->databaseConnection->getHeadings ($this->settings['database'], $table);
		if ($additionalHeadings) {$headings = array_merge ($headings, $additionalHeadings);}
		
		# If exporting, render then end
		#!# The output buffering (ob_* functions) is necessary because of the header/tabs/etc. being echoed
		if ($exportFormat == 'csv') {
			$data = $this->addAdditionalBookingsExportFields ($data);
			ob_end_clean ();
			ob_start ();
			require_once ('csv.php');
			csv::serve ($data, $table, true, $headings);
			ob_end_flush ();
			exit;
		}
		
		# Decorate the links
		foreach ($data as $key => $entry) {
			$urlId = (isSet ($entry['moniker']) ? $entry['moniker'] : $key);	// Prefer URL monikers if supplied
			$data[$key]['id'] = "<a href=\"{$this->baseUrl}/{$this->action}/{$urlId}/edit.html\" class=\"actions\">" . '<img src="/images/icons/page_white_edit.png" alt="" class="icon" /> &nbsp; ' . "<strong>{$key}</strong></a>";
		}
		
		# Add direct editing links, unless disabled
		if ($editingLinks) {
			foreach ($data as $key => $entry) {
				$urlId = (isSet ($entry['moniker']) ? $entry['moniker'] : $key);	// Prefer URL monikers if supplied
				$data[$key]['View']		= "<a title=\"View\" href=\"{$this->baseUrl}/{$this->action}/{$urlId}/\">" . '<img src="/images/icons/page_white.png" alt="View" class="icon" /></a>';
				$data[$key]['Edit']		= "<a title=\"Edit\" href=\"{$this->baseUrl}/{$this->action}/{$urlId}/edit.html\">" . '<img src="/images/icons/page_white_edit.png" alt="Edit" class="icon" /></a>';
				$data[$key]['Duplicate']	= "<a title=\"Duplicate\" href=\"{$this->baseUrl}/{$this->action}/{$urlId}/clone.html\">" . '<img src="/images/icons/page_copy.png" alt="Duplicate" class="icon" /></a>';
				$data[$key]['Delete']	= "<a title=\"Delete\" href=\"{$this->baseUrl}/{$this->action}/{$urlId}/delete.html\">" . '<img src="/images/icons/page_white_delete.png" alt="Delete" class="icon" /></a>';
			}
		}
		
		# Show the HTML
		$html  = "\n" . '<!-- Enable table sortability: --><script language="javascript" type="text/javascript" src="/sitetech/sorttable.js"></script>';
		$html .= application::htmlTable ($data, $headings, 'lines sortable datalist" id="sortable', $keyAsFirstColumn = false, false, $allowHtml = true, $showColons = false, $addCellClasses = true, $addRowKeyClasses = false, $onlyFields = array (), $compress = false, $showHeadings = true, $encodeEmailAddress = true);
		
		# Return the HTML
		return $html;
	}
	
	
	# Function to add additional computed fields to a bookings CSV export
	private function addAdditionalBookingsExportFields ($data)
	{
		# Add calculated timePeriodHours field (difference of startTime and untilTime)
		$today = date ('Y-m-d');
		foreach ($data as $id => $booking) {
			$timePeriodHours = ((strtotime ($today . ' ' . $booking['untilTime']) - strtotime ($today . ' ' . $booking['startTime'])) / 3600);
			$data[$id] = application::array_insert_value ($data[$id], 'timePeriodHours', $timePeriodHours, 'untilTime');
		}
		
		# Return the modified data array
		return $data;
	}
	
	
	# Function to substitute names in a table
	private function substituteUseridTokensToNames ($data, $fieldsToConvert, $hyperlinked = false)
	{
		# End if no data
		if (!$data) {return $data;}
		
		# Ensure there are user fields present, and if not, take no action
		$fields = array_keys (reset ($data));
		$userFieldsPresent = array_intersect ($fields, $fieldsToConvert);
		if (!$userFieldsPresent) {return $data;}
		
		# Extract all the token strings (e.g. '|abc12|xyz89|') from the user field(s) in the data
		$tokenStrings = array ();
		foreach ($data as $key => $entry) {
			foreach ($userFieldsPresent as $field) {
				$tokenStrings[] = $data[$key][$field];
			}
		}
		$tokenStrings = array_unique ($tokenStrings);
		
		# Convert to an array of tokenstring=>userIds
		$usersPerTokenString = application::splitCombinedTokenList ($tokenStrings, '|', $lookupMode = true);
		
		# Substitute the names; no sorting by surname is done as the order in the data should be considered explicit (i.e. first name is potentially most important)
		$people = $this->getPeople ();
		foreach ($usersPerTokenString as $tokenString => $users) {
			foreach ($users as $index => $userId) {
				if (isSet ($people[$userId])) {
					$usersPerTokenString[$tokenString][$index] = ($hyperlinked ? "<a href=\"{$this->baseUrl}/people/" . htmlspecialchars (urlencode ($userId)) . '/">' . htmlspecialchars ($people[$userId]['name']) . '</a>' : $people[$userId]['name']);
				}
			}
			$usersPerTokenString[$tokenString] = array_unique ($usersPerTokenString[$tokenString]);
			$usersPerTokenString[$tokenString] = application::commaAndListing ($usersPerTokenString[$tokenString]);
		}
		
		# Do a second parse of the data and perform the substitutions
		foreach ($data as $key => $entry) {
			foreach ($userFieldsPresent as $field) {
				$tokenString = $data[$key][$field];
				if (strlen ($tokenString)) {	// i.e. avoid value being NULL or empty
					$data[$key][$field] = $usersPerTokenString[$tokenString];
				}
			}
		}
		
		# Return the modified data
		return $data;
	}
	
	
	# Function to substitute shortnames for areas of activity in a dataset
	private function substituteAreaOfActivityShortnames ($data, $beforeField = false)
	{
		# End if no data
		if (!$data) {return $data;}
		
		# Get the list of areas of activity
		$areasOfActivity = array ();
		foreach ($data as $key => $entry) {
			if (strlen ($entry['activityId'])) {
				$areasOfActivity[] = $entry['activityId'];
			}
		}
		$areasOfActivity = array_unique ($areasOfActivity);
		
		# Get the activities hierarchy
		$activitiesHierarchy = $this->getActivities ();
		
		#!# Various checks needed in this function
		#!# End if no hierarchy?
		#!# Seems to have the first one shown
		# Get the ancestors of each node
		$activityAncestors = array ();
		foreach ($areasOfActivity as $areaOfActivityId) {
			$activityAncestors[$areaOfActivityId] = $this->hierarchy->getAncestors ($areaOfActivityId);
		}
		
		# Get the shortnames
		$shortnames = array ();
		foreach ($activityAncestors as $areaOfActivityId => $ancestors) {
			$shortnames[$areaOfActivityId] = array ();
			#!# Need to check what happens if none exist
			$ancestors = array_reverse ($ancestors);
			foreach ($ancestors as $ancestorId => $ancestor) {
				if (strlen ($ancestor['shortname'])) {
					$shortnames[$areaOfActivityId][] = $ancestor['shortname'];
				}
			}
			$shortnames[$areaOfActivityId] = implode (': ', $shortnames[$areaOfActivityId]);
		}
		
		# Add in the shortnames
		foreach ($data as $key => $entry) {
			$areaOfActivityId = $entry['activityId'];
			if ($beforeField) {
				$data[$key] = application::array_insert_value ($data[$key], 'activityNamePrefix', $shortnames[$areaOfActivityId], false, $beforeField);
			} else {
				$data[$key]['activityNamePrefix'] = $shortnames[$areaOfActivityId];
			}
		}
		
		# Return the modified dataset
		return $data;
	}
	
	
	# Function to remove unwanted standard fields
	private function removeUnwantedFields ($data)
	{
		# Remove the unwanted fields
		foreach ($data as $key => $entry) {
			foreach ($this->hideFields as $field) {
				if (isSet ($data[$key][$field])) {
					unset ($data[$key][$field]);
				}
			}
		}
		
		# Return the data
		return $data;
	}
	
	
	# Show item
	private function crudEditingView ($table, $id)
	{
		# Start the HTML
		$html = '';
		
		# Get the data for the item or end
		if (!$data = $this->crudGetDataFromGetId ($table, $id, $html)) {return $html;}
		
		# Show the record
		$html .= $this->crudRecordDisplay ($data, $data['id'], $table);
		
		# Return the HTML
		return $html;
	}
	
	
	# CRUD helper function to get the data for an item based on an ID in the URL
	private function crudGetDataFromGetId ($table, $id, &$html)
	{
		# Get the data for this record
		$conditions = array ('id' => $id);
		$data = $this->databaseConnection->selectOne ($this->settings['database'], $table, $conditions);
		
		# End if none
		if (!$data) {
			$html .= "\n<p>There is no such item <em>" . htmlspecialchars ($id) . "</em>. You may wish to <a href=\"{$this->baseUrl}/{$this->action}/add.html?id=" . htmlspecialchars (urlencode ($id)) . "\">create it</a>.</p>";
			return false;
		}
		
		# Return the data
		return $data;
	}
	
	
	# Helper function to show a record
	private function crudRecordDisplay ($data, $id, $table, $class = false)
	{
		# Start the HTML
		$html = '';
		
		# Get the headings
		$headings = $this->databaseConnection->getHeadings ($this->settings['database'], $table);
		
		# Remove unwanted fields
		$hideFields = array ('id', 'createdAt', );
		foreach ($hideFields as $field) {
			if (isSet ($data[$field])) {
				unset ($data[$field]);
			}
		}
		
		# Compile the HTML
		$html .= "\n<p>Here is item <em>" . htmlspecialchars ($id) . '</em>:</p>';
		$html .= application::htmlTableKeyed ($data, $headings);
		
		# Surround with a box if required
		if ($class) {
			$html = "\n<div class=\"{$class}\">" . $html . '</div>';
		}
		
		# Return the HTML
		return $html;
	}
	
	
	# Search
	private function crudEditingSearch ($table, $id, $dataBindingParameters, $fixedData)
	{
		# Obtain the standard dataBinding parameters
		$dataBindingParameters = $this->getDataBindingParameters ($dataBindingParameters, $table);
		
		# Make changes to standard dataBinding parameters
		$dataBindingParameters['attributes']['bookedForUserid']['multiple'] = false;
		$dataBindingParameters['attributes']['bookedForUserid']['expandable'] = false;
		$dataBindingParameters['attributes']['bookedForUserid']['separator'] = false;
		$dataBindingParameters['attributes']['bookedForUserid']['defaultPresplit'] = false;
		//application::dumpData ($dataBindingParameters['attributes']['bookedForUserid']);
		
		# Create settings for multisearch
		$settings = array (
			'description'						=> strtolower ($this->actions[$this->action]['description']),
			'databaseConnection'				=> $this->databaseConnection,
			'baseUrl'							=> $this->baseUrl . "/{$this->action}/search.html",
			'database'							=> $this->settings['database'],
			'table'								=> $table,
			'dataBindingParameters'				=> $dataBindingParameters,
			'orderBy'							=> 'id',
			'mainSubjectField'					=> 'name',
			// 'excludeFields' is already appearing through $dataBindingParameters
			'showFields'						=> array (),
			'recordLink'						=> $this->baseUrl . "/{$this->action}/%id/",
			'paginationRecordsPerPage'			=> $this->settings['paginationRecordsPerPage'],
			'searchPageInQueryString'			=> true,
			'ignoreKeys'						=> array ('do'),
			'jQueryLoaded'						=> true,
			'exportingEnabled'					=> false,
			'headingLevel'						=> false,
			'resultsContainerClass'				=> false,
			'resultRenderer'					=> array ($this, 'dataListing'),
		);
		
		# Load and run the multisearch facility
		require_once ('multisearch.php');
		$multisearch = new multisearch ($settings);
		$html = $multisearch->getHtml ();
		
		# Return the HTML
		return $html;
	}
	
	
	# Function to define a minisearch
	private function minisearch ($what = false, $action = false)
	{
		# Determine the action
		if (!$action) {$action = $this->action;}
		
		# End if not a booking
		#!# Replace with a proper search that filters to the relevant object type
		if ($action != 'bookings') {return false;}
		
		# Compile the HTML and return it
		$submittedValue = (isSet ($_GET['search']) ? htmlspecialchars ($_GET['search']) : '');
		/*
		return $html = "\n\n" . '<form class="minisearch" method="post" name="minisearchform" action="' . $this->baseUrl . "/{$action}/search.html" . '" enctype="application/x-www-form-urlencoded" accept-charset="UTF-8">
			<input name="search" type="search" size="30" value="' . $submittedValue . '" /> <input type="submit" value="Search' . ($what ? ' ' . htmlspecialchars ($what) : '!') . '" class="button" />
		</form>' . "\n";
		*/
		return $html = "\n\n" . '<form class="minisearch" method="post" name="minisearchform" action="' . $this->baseUrl . "/{$action}/search.html" . '" enctype="application/x-www-form-urlencoded" accept-charset="UTF-8">
			<input name="search" type="search" size="30" value="' . $submittedValue . '" placeholder="Title of booking" /> <input type="submit" value="Search booking titles" class="button" />
		</form>' . "\n";
	}
	
	
	# Addition
	private function crudEditingAdd ($table, $id, $dataBindingParameters, $fixedData = array (), $cloneMode = false, $requestMode = false)
	{
		# Start the HTML
		$html = '';
		
		# In clone mode, obtain the data for the item being cloned, but strip out its ID
		$data = array ();
		if ($cloneMode) {
			if (!$data = $this->crudGetDataFromGetId ($table, $id, $html)) {return $html;}
			unset ($data['id']);
		}
		
		# Show the form or end
		if (!$result = $this->dataForm ($html, $table, $dataBindingParameters, $fixedData, $data, $additionalResults, $cloneMode, $requestMode)) {return $html;}
		
		# Insert the record
		$this->databaseConnection->insert ($this->settings['database'], $table, $result);
		
		# Get the record number
		$id = $this->databaseConnection->getLatestId ();
		
		# Determine the link ID
		$linkId = (isSet ($result['moniker']) ? $result['moniker'] : $id);
		
		# Deal with additional results, if any, and obtain a revised ID which will now be a list
		$linkId = $this->insertAdditionalResults ($additionalResults, $table, $id, $linkId);
		
		# Refresh the user database
		#!# This is non-generic; need to enable the caller to do this by passing back the result
		$this->refreshUserDatabase ($result);
		
		# Confirm and redirect (with a flash) to the view page
		$html = $this->flashMessage ($linkId, 'add');
		
		# Return the HTML
		return $html;
	}
	
	
	# Editing
	private function crudEditingEdit ($table, $id, $dataBindingParameters, $fixedData)
	{
		# Start the HTML
		$html = '';
		
		# Get the data for the item or end
		if (!$data = $this->crudGetDataFromGetId ($table, $id, $html)) {return $html;}
		
		# Show the form or end
		if (!$result = $this->dataForm ($html, $table, $dataBindingParameters, $fixedData, $data, $additionalResults)) {return $html;}
		
		# Insert the record
		#!# Would be useful if database::update() $conditions could default to array(id=>$id) if just $id supplied rather than an array
		$id = $data['id'];
		$conditions = array ('id' => $id);
		$this->databaseConnection->update ($this->settings['database'], $table, $result, $conditions);
		
		# Determine the link ID
		$linkId = (isSet ($result['moniker']) ? $result['moniker'] : $id);
		
		# Deal with additional results, if any, and obtain a revised ID which will now be a list
		$linkId = $this->insertAdditionalResults ($additionalResults, $table, $id, $linkId);
		
		# Refresh the user database
		#!# This is non-generic; need to enable the caller to do this by passing back the result
		$this->refreshUserDatabase ($result);
		
		# Confirm and redirect (with a flash) to the view page
		$html .= $this->flashMessage ($linkId, 'edit');
		
		# Return the HTML
		return $html;
	}
	
	
	# Function to process additional results, if any
	private function insertAdditionalResults ($additionalResults, $table, $id, $linkId)
	{
		/*
		# If there is a more specific processor, use this
		$function = __FUNCTION__ . ucfirst ($this->action);
		if (method_exists ($this, $function)) {
			return $this->{$function} ($additionalResults, $table, $id, $linkId);
		}
		*/
		
		# End if none
		if (!$additionalResults) {return $linkId;}
		
		# Insert each item, obtaining the linkId as we go
		$linkIds = array ($linkId);
		foreach ($additionalResults as $result) {
			$this->databaseConnection->insert ($this->settings['database'], $table, $result);
			$linkIds[] = (isSet ($result['moniker']) ? $result['moniker'] : $this->databaseConnection->getLatestId ());
		}
		$linkIds = implode (',', $linkIds);
		
		# Return the linkIds
		return $linkIds;
	}
	
	
	# Function to set a flash message
	private function flashMessage ($linkId, $do = false)
	{
		# Start the HTML
		$html = '';
		
		# Set the description
		$descriptions = array (
			'add' => 'created',
			'edit' => 'updated',
		);
		
		# Determine if this is a list of IDs rather than a single item
		$multipleIds = (bool) (substr_count ($linkId, ','));
		
		# Determine the redirection location
		$linkId = htmlspecialchars ($linkId);	// urlencode seems not to be needed, as the list is not part of a query string
		$redirectTo = "{$this->baseUrl}/{$this->action}/{$linkId}/";
		
		# Set a redirection message
		$recordDescription = ($multipleIds ? 'records # ' : 'record ') . (ctype_digit ($linkId) ? '#' : '') . $linkId;
		if ($do) {$recordDescription = "<a href=\"{$redirectTo}\">{$recordDescription}</a>";}
		$message = "\n<p class=\"flashmessage\"><img src=\"/images/icons/tick.png\" class=\"icon\" alt=\"\" /> Thanks; {$recordDescription} " . ($multipleIds ? 'have' : 'has') . " been successfully " . ($multipleIds ? 'saved' /* One may be an update and the others inserts, so 'saved' is simpler */ : '%s') . ".</p>";
		
		# If an achieved action has been set, set the flash
		$cookiePath = $this->baseUrl . '/';
		if ($do) {
			
			# Set a flash and redirect
			$html = application::setFlashMessage ($this->action, $do, $redirectTo, sprintf ($message, $descriptions[$do]), $cookiePath);
			
		# Otherwise, retrieve the message
		} else {
			if ($do = application::getFlashMessage ($this->action, $cookiePath)) {
				if (isSet ($descriptions[$do])) {
					$html = sprintf ($message, $descriptions[$do]);
				}
			}
		}
		
		# Return the HTML
		return $html;
	}
	
	
	# Cloning; basically the same as addition but with data pre-filled
	private function crudEditingClone ($table, $id, $dataBindingParameters, $fixedData)
	{
		# Same as addition
		$html = $this->crudEditingAdd ($table, $id, $dataBindingParameters, $fixedData, $cloneMode = true);
		
		# Return the HTML
		return $html;
	}
	
	
	# Deletion
	private function crudEditingDelete ($table, $id)
	{
		# Start the HTML
		$html = '';
		
		# Get the data for the item or end
		if (!$data = $this->crudGetDataFromGetId ($table, $id, $html)) {return $html;}
		
		# If there is a series field and it contains a value, run in series mode
		$seriesListing = $this->seriesListing ($data, $table);
		
		# Create the deletion form
		$formHtml = '';
		$form = new form (array (
			'displayRestrictions' => false,
			'formCompleteText' => false,
			'displayColons' => false,
		));
		$options = array ();
		$options['record'] = 'Yes, delete record #' . $data['id'];
		if ($seriesListing) {$options['series'] = $options['record'] . ' <strong>and</strong> the whole series';}
		$widgetType = ($seriesListing ? 'radiobuttons' : 'checkboxes');
		$form->{$widgetType} (array (
			'name'			=> 'confirm',
			'title'			=> 'Do you really want to delete the ' . ($seriesListing ? 'record(s)' : 'record') . ' below?',
			'values'		=> $options,
			'required' 		=> 1,
			'entities'		=> false,
		));
		$result = $form->process ($formHtml);
		
		# Assemble the HTML for the form and the record
		$html .= $formHtml;
		$html .= $this->crudRecordDisplay ($data, $data['id'], $table, 'graybox');
		
		# In series mode, show others in series
		$html .= $seriesListing;
		
		# Show the HTML
		if (!$result) {return $html;}
		
		# Determine the conditions
		if ($seriesListing && $result['confirm'] == 'series') {
			$conditions = array ('series' => $data['series']);
		} else {
			$conditions = array ('id' => $data['id']);	// The checkbox or a radiobutton must have been selected to get this far
		}
		
		# Delete the record/series
		$this->databaseConnection->delete ($this->settings['database'], $table, $conditions);
		
		# Confirm and link back
		$html = "\n<p>Thanks; the " . ($result['confirm'] == 'series' ? 'series' : 'record') . ' has now been deleted.</p>';
		
		# Return the HTML
		return $html;
	}
	
	
	# Function to create a listing of items in series mode
	private function seriesListing ($data, $table)
	{
		# If there is a series field and it contains a value, run in series mode
		$seriesId = (isSet ($data['series']) && strlen ($data['series']) ? $data['series'] : false);
		if (!$seriesId) {return false;}
		
		# Get the data
		$data = $this->databaseConnection->select ($this->settings['database'], $table, array ('series' => $seriesId), array (), true, 'id');
		
		# End if only one record in total
		if (count ($data) < 2) {return false;}
		
		# Create the listing
		$html  = "\n<h3>All entries in this series</h3>";
		$html .= "\n<p>There are additional linked records:</p>";
		$html .= $this->dataListing ($data, $table);
		
		# Return the HTML
		return $html;
	}
	
	
	# Function to refresh the user database upon CRUD submissions
	private function refreshUserDatabase ($result)
	{
		# End if not supported
		#!# This needs to be API-based
		if (!$this->settings['usersExternalDatabase']) {return false;}
		
		# Check for matches
		$userIds = array ();
		foreach ($result as $key => $value) {
			if (in_array ($key, $this->userFields)) {
				$usernames = application::splitCombinedTokenList ($value);
				$userIds += array_merge ($userIds, $usernames);
			}
		}
		
		# End if none of the fields are present, i.e. this is not a database object involving users
		if (!$userIds) {return;}
		
		# Insert/update the rows; insertMany cannot be used as the onDuplicateKeyUpdate is not supported in that interface
		foreach ($userIds as $userId) {
			$insert = array ('id' => $userId);
			$this->databaseConnection->insert ($this->settings['database'], 'people', $insert, $onDuplicateKeyUpdate = true);
		}
		
		# Update the names
		$this->updatePeopleNames ();
	}
	
	
	# Function to update people names in the database
	private function updatePeopleNames ()
	{
		# End if no people database
		#!# This needs to be API-based
		if (!$this->settings['usersExternalDatabase']) {return false;}
		
		#!# This needs to look at areasOfActivity.people also
		
		# Populate all records with real usernames in one go using a cross-update
		$query = "UPDATE {$this->settings['database']}.people
			LEFT JOIN {$this->settings['usersExternalDatabase']} ON {$this->settings['usersExternalDatabase']}.username = {$this->settings['database']}.people.id
			SET
				{$this->settings['database']}.people.title = {$this->settings['usersExternalDatabase']}.title,
				{$this->settings['database']}.people.forename = {$this->settings['usersExternalDatabase']}.forename,
				{$this->settings['database']}.people.surname = {$this->settings['usersExternalDatabase']}.surname,
				/* This compiled version ensures there is a 'name' field, so that it complies with a standard object type structure, and avoids us constantly having to compute it */
				{$this->settings['database']}.people.name = TRIM(CONCAT_WS(' ',{$this->settings['usersExternalDatabase']}.title,{$this->settings['usersExternalDatabase']}.forename,{$this->settings['usersExternalDatabase']}.surname))
			WHERE id REGEXP '{$this->settings['usernameRegexp']}'
		;";
		$this->databaseConnection->query ($query);
		
		# Get the names for records without real usernames
		$query = "SELECT id FROM {$this->settings['database']}.people WHERE id NOT REGEXP '{$this->settings['usernameRegexp']}';";
		$names = $this->databaseConnection->getPairs ($query);
		$updates = array ();
		foreach ($names as $name) {
			$update = $this->normaliseRealname ($name);
			if (!$update) {continue;}	// Skip if no update needed
			$updates[$name] = $update;
		}
		foreach ($updates as $id => $update) {
			$this->databaseConnection->update ($this->settings['database'], 'people', $update, array ('id' => $id));
		}
		
		# Set the name to be username if there is no name
		$query = "UPDATE people SET name = IF(name = '',id,name);";
		$this->databaseConnection->query ($query);
	}
	
	
	# Function to normalise a name (e.g. "John Smith" or "Smith, John") to a forename and surname
	private function normaliseRealname ($string)
	{
		/*
		# Tests
		$userIds = array (
			'spqr1',
			'Singularname',
			'Surname, Forename',
			'Surname, Forename Middlename',
			"Forename Surname",
			"Forename-Bar Surname",
			"Forename Middlename Surname",
			"Forename Middlename Surname-Bar",
		);
		*/
		
		# Trim the string
		$string = trim ($string);
		
		# If a real username, no update needed
		if ($this->isRealUsername ($string)) {return false;}
		
		# Case of "Singularname" (i.e. one name only)
		if (preg_match ('/^([^\s]+)$/', $string, $matches)) {
			return array (
				'forename' => trim ($matches[1]),
				'name' => trim ($matches[1]),
			);
		}
		
		# Case of "Surname, Forename" or "Surname, Forename Middlename" format
		if (preg_match ('/^([^,]+),(.+)$/', $string, $matches)) {
			return array (
				'forename' => trim ($matches[2]),
				'surname' => trim ($matches[1]),
				'name' => trim ($matches[2]) . ' ' . trim ($matches[1]),
			);
		}
		
		# Case of "Forename Surname" or "Forename Middlename Surname" format
		if (preg_match ('/^(.+)\s+([^\s]+)$/', $string, $matches)) {	// Regexps are greedy, so the \s will match the last one
			return array (
				'forename' => trim ($matches[1]),
				'surname' => trim ($matches[2]),
				'name' => trim ($matches[1]) . ' ' . trim ($matches[2]),
			);
		}
		
		# No match
		return false;
	}
	
	
	# Function to determine whether a username is a real username
	private function isRealUsername ($string)
	{
		# Do a match against the username regexp in the settings
		$delimiter = '/';
		$isRealUsername = preg_match ($delimiter . addcslashes ($this->settings['usernameRegexp'], $delimiter)  . $delimiter, $string);
		return $isRealUsername;
	}
	
	
	# Function to create a translation of tables to action URLs
	private function tableMonikers ()
	{
		# Create a translation of tables to action URLs
		$tableMonikers = array ();
		foreach ($this->actions as $action => $attributes) {
			$table = (isSet ($attributes['table']) ? $attributes['table'] : $action);	// Use the default action if the table is not present
			$tableMonikers[$table] = $action;
		}
		
		# Return the array
		return $tableMonikers;
	}
	
	
	# Function to assemble standard dataBinding parameters
	private function getDataBindingParameters ($dataBindingParameters, $table, $data = array (), $fixedData = array (), $cloneMode = false)
	{
		# Add to the supplied parameters
		$dataBindingParameters = $dataBindingParameters /* these supplied parameters take priority */ + array (
			'database' => $this->settings['database'],
			'table' => $table,
			'intelligence' => true,
			'int1ToCheckbox' => true,
			'simpleJoin' => true,
			'lookupFunctionParameters' => array (NULL, false, true, false, $firstOnly = true, array (), $this->tableMonikers ()),
			'lookupFunctionAppendTemplate' => "<a href=\"{$this->baseUrl}/" . "%table/\" class=\"noarrow\" tabindex=\"998\" title=\"Click here to open a new window for editing these values; then click on refresh.\" target=\"_blank\"> ...</a>%refreshtabindex999",
			'attributes' => array (),
			'data' => $data,
			'exclude' => array_keys ($fixedData),
			'truncate' => 60,	// Needed to stop areaOfActivity entries being truncated
			'editingUniquenessUniChecking' => (!$cloneMode),	// Disable the UNI checking if cloning
		);
		
		# Return the result
		return $dataBindingParameters;
	}
	
	
	# Data form
	private function dataForm (&$html, $table, $dataBindingParameters, $fixedData, $data, &$additionalResults = array (), $cloneMode = false, $requestMode = false)
	{
		# Load and instantiate the form library
		$form = new form (array (
			'nullText' => '',
			'databaseConnection' => $this->databaseConnection,
			'displayRestrictions' => false,
			'unsavedDataProtection' => true,
			'picker' => true,
			'autofocus' => (!$data),
			'jQuery' => false,		// Already loaded on the page
		));
		
		# Assemble the dataBinding parameters
		$dataBindingParameters = $this->getDataBindingParameters ($dataBindingParameters, $table, $data, $fixedData, $cloneMode);
		
		# In request mode, lock down certain fields
		if ($table == 'bookings') {
			if ($requestMode) {
				$dataBindingParameters['attributes']['notes']['value'] = 'foo';
				$dataBindingParameters['attributes']['draft']['values'] = array (1 => 'Draft');
				$dataBindingParameters['attributes']['draft']['default'] = 1;
				$dataBindingParameters['attributes']['draft']['editable'] = false;
				$dataBindingParameters['attributes']['requestedBy']['required'] = true;
				$dataBindingParameters['attributes']['requestedBy']['default'] = $this->user;
				$dataBindingParameters['attributes']['requestedBy']['editable'] = false;
			} else {
				$dataBindingParameters['exclude'][] = 'requestedBy';
			}
		}
		
		# Create the form widgets, data-binded against the database structure
		$form->dataBinding ($dataBindingParameters);
		
		#!# Move this block into a function that is called if present, passing in $form
		# Bookings: deal with the repeat fields
		if ($table == 'bookings') {
			
			# Add repetition fields (except in request mode)
			if (!$requestMode) {
				$form->number (array (
					'name'			=> 'repeatWeeks',
					'after'			=> 'untilTime',
					'title'			=> 'Repeat?',
					'maxlength'		=> 2,
					'size'			=> 4,
					'min'			=> 1,
					'max'			=> 52,
					'prepend'		=> 'for the following ',
					'append'		=> ' weeks, on:',
				));
				$form->checkboxes (array (
					'name'			=> 'repeatDays',
					'after'			=> 'untilTime',		// These cannot be changed so use the same one
					'title'			=> false,
					'values'		=> $this->dayNamesFormatted (true),	// All are shown, regardless of the showWeekends setting
					'required' 		=> false,
					'linebreaks'	=> false,
					'output'		=> array ('processing' => 'special-setdatatype'),
				));
				$form->validation ('all', array ('repeatWeeks', 'repeatDays'));
			}
			
			# Deal with additional constraints against unfinalised data
			if ($unfinalisedData = $form->getUnfinalisedData (true)) {	// True is set so that the special-setdatatype is used
				
				# Ensure that the startTime is earlier than the endTime
				if ($errorMessage = $this->timeOrderingInvalid ($unfinalisedData)) {
					$form->registerProblem ('timeOrderingInvalid', $errorMessage);
				}
				
				# Prevent booking clashes (single item)
				$editingId = (($data && isSet ($data['id'])) ? $data['id'] : false);
				$bookingClashes = $this->bookingClash ($unfinalisedData, $editingId);
				
				# If there is a proposed series, prevent booking clashes for the additional results
				if (!$bookingClashes) {	// Don't check if a single clash has already been found
					list ($resultProposed, $additionalResultsProposed) = $this->createSeries ($unfinalisedData);
					if ($additionalResultsProposed) {
						foreach ($additionalResultsProposed as $additionalResultProposed) {
							if ($bookingClashes = $this->bookingClash ($additionalResultProposed)) {
								break;	// No point checking any more results in this series
							}
						}
					}
				}
				
				# Allow booking clash overriding, by injecting in an extra checkbox
				if ($bookingClashes) {
					$totalClashes = count ($bookingClashes);
					$form->checkboxes (array (
						'name'			=> 'overrideClash',
						'after'			=> '_heading1',
						'title'			=> 'Override clash checking?',
						'values'		=> array ('1' => 'Yes, override ' . ($totalClashes == 1 ? 'clash' : "clashes ({$totalClashes})")),
						'required' 		=> false,
						'output' 		=> array ('processing' => 'compiled'),
						'discard'		=> true,
					));
					$unfinalisedDataWithClashOverrideCheckbox = $form->getUnfinalisedData (true);
					if (!$unfinalisedDataWithClashOverrideCheckbox['overrideClash']) {
						foreach ($bookingClashes as $index => $errorMessage) {
							$form->registerProblem ('bookingClash' . ($index + 1), $errorMessage . ' You can override clash checking below.');
						}
					}
				}
			}
		}
		
		# For term dates, enforce ordering
		if ($table == 'terms') {
			if ($unfinalisedData = $form->getUnfinalisedData ()) {
				if ($unfinalisedData['startDate'] && $unfinalisedData['untilDate']) {
					if ($unfinalisedData['startDate'] >= $unfinalisedData['untilDate']) {
						$form->registerProblem ('dateOrdering', 'The last day of term must be after the first day of term.');
					}
				}
			}
		}
		
		# Process the form
		$result = $form->process ($html);
		
		# Inject fixed data, which will have been excluded from form widget creation
		#!# Question of whether this should apply only to edit or add/edit (e.g. case of creator vs last-edited-by)
		if ($result) {
			if ($fixedData) {
				foreach ($fixedData as $key => $value) {
					$result[$key] = $value;
				}
			}
		}
		
		# Deal with series handling for the bookings table
		if ($table == 'bookings') {
			list ($result, $additionalResults) = $this->createSeries ($result);
		}
		
		# Save the edit date when dealing with bookings
		if ($table == 'bookings') {
			$this->saveImplicitViewDates ($result['date'], $this->userProfile['weeksAhead']);
		}
		
		# Return the result
		return $result;
	}
	
	
	# Helper function to get a shortened version of the day names
	private function dayNamesFormatted ($shorten = 3, $uppercase = true)
	{
		# Create the list
		$dayNames = array ();
		foreach ($this->days as $id => $day) {
			$dayNames[$id] = $day;
			if ($shorten) {
				$dayNames[$id] = substr ($dayNames[$id], 0, 3);
			}
			if ($uppercase) {
				$dayNames[$id] = ucfirst ($dayNames[$id]);
			}
		}
		
		# Return the list
		return $dayNames;
	}
	
	
	# Function to ensure that the end time/date is later than the start time/date
	private function timeOrderingInvalid ($data, $datesMode = false)
	{
		# Determine whether to use times or dates
		$type = ($datesMode ? 'Date' : 'Time');
		$typeLabel = strtolower ($type);
		
		# End if no start or until time
		if (!strlen ($data["start{$type}"]) || !strlen ($data["until{$type}"])) {return false;}
		
		# Invalid if the start time/date is the same or greater than the until time/date; these are strings like 12:20:00, which the >= operator copes with
		if ($datesMode) {
			$invalid = ($data["start{$type}"] > $data["until{$type}"]);
		} else {
			$invalid = ($data["start{$type}"] >= $data["until{$type}"]);
		}
		if ($invalid) {return "The start {$typeLabel} must be later than the end {$typeLabel}.";}
		
		# No problems
		return false;
	}
	
	
	# Function to get additional results for bookings
	private function createSeries ($result)
	{
		# Obtain series parameters
		$repeatWeeks = $result['repeatWeeks'];
		$repeatDays = explode (',', $result['repeatDays']);
		unset ($result['repeatWeeks']);
		unset ($result['repeatDays']);
		
		# If there is no repeat field, return without processing
		if (!$repeatWeeks || !$repeatDays) {	// Actually only one needs to be checked because of the validation rule
			return array ($result, $additionalResults = array ());
		}
		
		# Set a series ID, which is a microtime reading, which should ensure sufficient uniqueness; e.g. "0.77452800 1345660676" will become 1345660676077452800 (19 characters long)
		list ($microseconds, $timestamp) = explode (' ', microtime ());
		$result['series'] = str_replace ('.', '', $timestamp . $microseconds);	// Add into the array - was not there before
		
		# Get the Monday of each of the weeks after the submitted one
		$weeksMondayTimestamp = timedate::getMondays ($repeatWeeks, false, true, $timestamp = strtotime ($result['date'] . ' 01:01:01'), $excludeCurrent = true);
		
		# For each Monday, determine the timestamp of each extra day
		$additionalDays = array ();
		foreach ($weeksMondayTimestamp as $weeksMondayTimestamp) {
			
			# For each of the days, determine a timestamp, with Monday being 1
			foreach ($repeatDays as $daysFrom) {
				$timestamp = $weeksMondayTimestamp + ((60*60*24) * ($daysFrom - 1));	// e.g. for Tuesday, add one lot of 86400 seconds to the Monday timestamp
				$additionalDays[] = date ('Y-m-d', $timestamp);
			}
		}
		
		# For each additional day, create a clone but using this replacement date
		$additionalResults = array ();
		$resultClone = $result;
		foreach ($additionalDays as $additionalDay) {
			unset ($resultClone['id']);
			$resultClone['date'] = $additionalDay;
			$additionalResults[] = $resultClone;
		}
		
		# Return the additional results and the modified main result
		return array ($result, $additionalResults);
	}
	
	
	# Function to check for booking clashes
	private function bookingClash ($formData, $editingId = false)
	{
		# Do not run checks if all the time-related fields are not present
		$timeFields = array ('date', 'startTime', 'untilTime');
		foreach ($timeFields as $field) {
			if (!strlen ($formData[$field])) {
				return false;
			}
		}
		
		# Determine clash fields, and whether they are people-related
		$clashFields = array (
			'roomId' => false,
			'bookedForUserid' => true,
			'areaOfActivityId' => true,
		);
		
		# Require at least one of the clash-related fields
		$clashFieldsPresent = array ();
		foreach ($clashFields as $field => $peopleRelated) {
			if (strlen ($formData[$field])) {
				$clashFieldsPresent[] = $field;
			}
		}
		if (!$clashFieldsPresent) {return false;}
		
		# Sanitise each input
		#!# Need to replace this with prepared statements
		$inputFields = array_merge ($timeFields, array_keys ($clashFields));
		$formDataQuoted = array ();
		foreach ($inputFields as $field) {
			$formDataQuoted[$field] = $this->databaseConnection->quote ($formData[$field]);
		}
		
		# Create an SQL snippet covering each of the clash fields
		$clashFieldsClauses = array ();
		foreach ($clashFieldsPresent as $field) {
			
			# Special handling for people-related fields
			$isPeopleRelated = $clashFields[$field];
			if ($isPeopleRelated) {
				
				# Determine the people involved, either those directly listed, or the area of activity involved
				switch ($field) {
					case 'bookedForUserid':
						$usernamesString = $formData[$field];
						break;
					case 'areaOfActivityId':
						$usernamesString = $this->peopleInActivity ($formData[$field]);
						break;
				}
				
				# If usernames are being supplied, assemble a where clause based on these users
				if ($usernamesString) {
					$usernames = application::splitCombinedTokenList ($usernamesString);
					$clashFieldsClauses[$field] = $this->personClashCheckingWhereClause ($usernames);
				}
				continue;
			}
			
			# Otherwise, standard handling
			$clashFieldsClauses[$field] = "({$field} = {$formDataQuoted[$field]})";
		}
		$clashFieldsClauses = implode (' OR ', $clashFieldsClauses);
		
		# Obtain the data
		$query = "SELECT
				bookings.id,
				bookings.roomId,
				bookings.bookedForUserid,
				bookings.areaOfActivityid,
				areaOfActivity.people AS areaOfActivityPeople,
				DATE_FORMAT(date,'%W, %D %M, %Y') AS dateFormatted,
				LOWER(DATE_FORMAT(date,'/%Y/%M/%e/')) AS dateLinkFormatted,
				REPLACE(LOWER(DATE_FORMAT(CONCAT(date,' ',startTime),'%l.%i%p')),'.00','') as startTimeFormatted,
				REPLACE(LOWER(DATE_FORMAT(CONCAT(date,' ',untilTime),'%l.%i%p')),'.00','') as untilTimeFormatted,
				CONCAT_WS(', ',rooms.name,buildings.name) AS roomName
			FROM bookings
			LEFT JOIN rooms ON bookings.roomId = rooms.id
			LEFT JOIN areaOfActivity ON bookings.areaOfActivityid = areaOfActivity.id
			LEFT JOIN buildings ON rooms.buildingId = buildings.id
			WHERE
				    date = {$formDataQuoted['date']}
				AND (
					/* Check for date range clashes - see http://stackoverflow.com/questions/8914457/ */
					/* Originally:
					   (startTime <= {$formDataQuoted['startTime']} AND untilTime >  {$formDataQuoted['startTime']})
					OR (startTime <  {$formDataQuoted['untilTime']} AND untilTime >= {$formDataQuoted['untilTime']})
					OR (startTime >= {$formDataQuoted['startTime']} AND untilTime <= {$formDataQuoted['untilTime']})
					*/
					   (CAST(CONCAT(date,' ',startTime) AS DATETIME) <= CAST(CONCAT({$formDataQuoted['date']},' ',{$formDataQuoted['startTime']}) AS DATETIME) AND CAST(CONCAT(date,' ',untilTime) AS DATETIME) >  CAST(CONCAT({$formDataQuoted['date']},' ',{$formDataQuoted['startTime']}) AS DATETIME) )
					OR (CAST(CONCAT(date,' ',startTime) AS DATETIME) <  CAST(CONCAT({$formDataQuoted['date']},' ',{$formDataQuoted['untilTime']}) AS DATETIME) AND CAST(CONCAT(date,' ',untilTime) AS DATETIME) >= CAST(CONCAT({$formDataQuoted['date']},' ',{$formDataQuoted['untilTime']}) AS DATETIME) )
					OR (CAST(CONCAT(date,' ',startTime) AS DATETIME) >= CAST(CONCAT({$formDataQuoted['date']},' ',{$formDataQuoted['startTime']}) AS DATETIME) AND CAST(CONCAT(date,' ',untilTime) AS DATETIME) <= CAST(CONCAT({$formDataQuoted['date']},' ',{$formDataQuoted['untilTime']}) AS DATETIME) )
				)
				AND (
					{$clashFieldsClauses}
				)
				" . ($editingId ? 'AND bookings.id != ' . $this->databaseConnection->quote ($editingId) : '') . "
		;";
		$data = $this->databaseConnection->getData ($query);
		
		/*
		application::dumpData ($query);
		application::dumpData ($data);
		application::dumpData ($this->databaseConnection->error ());
		die;
		*/
		
		# If there are any clashes, return the details
		$clashMessages = array ();
		if ($data) {
			$existingBooking = $data[0];	// This will always be present; just use the first if there is more than one, so that the user has to work through one problem at a time
			
			# Define links to each component of the error message
			$roomDescriptionLink = "<a href=\"{$this->baseUrl}/rooms/{$existingBooking['roomId']}/\">" . htmlspecialchars ($existingBooking['roomName']) . '</a>';
			$timePeriodDescription = "from {$existingBooking['startTimeFormatted']}-{$existingBooking['untilTimeFormatted']} on <a href=\"{$this->baseUrl}{$existingBooking['dateLinkFormatted']}\">{$existingBooking['dateFormatted']}</a>";
			
			# Room clashes
			if ($formData['roomId'] == $existingBooking['roomId']) {
				$anotherBookingDescription = "<a href=\"{$this->baseUrl}/bookings/{$existingBooking['id']}/\">another booking</a>";
				$clashMessages[] = "{$roomDescriptionLink} is already in use for {$anotherBookingDescription} {$timePeriodDescription}.";
			}
			
			# If there is a user/users clash, return that; we have to check each individually
			$existingBookingPeopleFields = array ($existingBooking['bookedForUserid'], $existingBooking['areaOfActivityPeople']);
			$proposedPeopleFields = array ($formData['bookedForUserid'], $this->peopleInActivity ($formData['areaOfActivityId']));
			if ($commonUsers = $this->commonUsers ($existingBookingPeopleFields, $proposedPeopleFields)) {
				$commonUsersString = application::commaAndListing ($commonUsers);
				$anotherBookingDescription = "<a href=\"{$this->baseUrl}/bookings/{$existingBooking['id']}/\">another appointment</a>";
				$clashMessages[] = "{$commonUsersString} already " . (count ($commonUsers) == 1 ? 'has' : 'have') . " {$anotherBookingDescription} {$timePeriodDescription}, in {$roomDescriptionLink}.";
			}
			
			# Fallback where the exact reason could not be determined; this should never happen
			if (!$clashMessages) {
				$clashMessages[] = "A clash was detected with <a href=\"{$this->baseUrl}/bookings/{$existingBooking['id']}/\">booking #{$existingBooking['id']}</a>, although the exact reason could not be determined.";
			}
		}
		
		# Return the messages (if any)
		return $clashMessages;
	}
	
	
	# Function to get the people in the activity
	private function peopleInActivity ($activityId)
	{
		# Get the activity or end
		if (!$item = $this->getActivities ($activityId)) {return false;}
		
		# Return the string
		return $item['people'];
	}
	
	
	# Function to return the common users in two sets of pipe-separated user strings; this ensures that |abc12|mno24|xyz89| matches against e.g. |xyz89|abc12|mno24|
	private function commonUsers ($existing, $proposed)
	{
		# If either is an array, run each string in the array together
		if (is_array ($existing)) {$existing = implode ('', $existing);}
		if (is_array ($proposed)) {$proposed = implode ('', $proposed);}
		
		# Convert each to an array
		$existing = application::splitCombinedTokenList ($existing);	// This will cope with a double-pipe, e.g. |abc12| and |xyz12|mno24| becoming |abc12||xyz12|mno24|
		$proposed = application::splitCombinedTokenList ($proposed);
		
		# Find the common users
		$commonUsers = array_intersect ($existing, $proposed);
		
		# End if none
		if (!$commonUsers) {return false;}
		
		# Get the names on each person
		$names = $this->getPeople ($commonUsers, true, false);
		
		# Format each person, using the compiled name in the database but not showing the username
		$commonUsersFormatted = array ();
		foreach ($commonUsers as $userId) {
			$nameFormatted = (isSet ($names[$userId]) ? $names[$userId] : htmlspecialchars ($userId));
			$commonUsersFormatted[$userId] = "<a href=\"{$this->baseUrl}/people/" . htmlspecialchars (urlencode ($userId)) . '/">' . $nameFormatted . '</a>';
		}
		
		# Return the list
		return $commonUsersFormatted;
	}
	
	
	
	/* ---------------------------- */
	/*     Helper data functions    */
	/* ---------------------------- */
	
	
	# Function to determine if the current user is an editor
	private function userIsEditor ()
	{
		# End if there is no user
		if (!$this->user) {return false;}
		
		#!# An admin should be possible to be an editor
		
		# Get the data
		$result = $this->databaseConnection->selectOne ($this->settings['database'], 'editors', array ('userId' => $this->user));
		
		# Return the result
		return (bool) $result;
	}
	
	
	# Helper function to get the activities hierarchy
	private function getActivities ($id = false, $familyNodeId = false, $hideFromNew = false, $familyNodeIdIncludeAncestors = true, &$errorHtml = false)
	{
		# Ensure any supplied ID is numeric
		if ($id !== false && !ctype_digit ($id)) {return false;}
		
		# Determine the table name
		$table = 'areaOfActivity';
		
		# Get the data
		$databaseFunction = ($id ? 'selectOne' : 'select');
		$conditions = ($id ? array ('id' => $id) : array ());
		if ($hideFromNew) {$conditions['hideFromNew'] = NULL;}	// i.e. will become IS NULL
		$data = $this->databaseConnection->{$databaseFunction} ($this->settings['database'], $table, $conditions, array (), true, $orderBy = 'parentId,name');
		
		#!# Natsort the data
		
		# End if none
		if (!$data) {
			$errorHtml = "<p>There are no {$table} entries so far. You may wish to <a href=\"{$this->baseUrl}/{$this->action}/add.html\">add one</a>.</p>";
			return false;
		}
		
		# Do regrouping as a hierarchy if fetching all data
		if (!$id) {
			
			# Add support for managing a hierarchical structure
			require_once ('hierarchy.php');
			$this->hierarchy = new hierarchy ($data);
			
			# Define the activities list values
			#!# Currently there is the problem that if a parent with children is marked has its hideFromNew flag ticked, the children have no parent, resulting in "Error: Not all items in the data have a parent which exists"
			if ($familyNodeId) {
				$data = $this->hierarchy->getFamily ($familyNodeId, $familyNodeIdIncludeAncestors);
			} else {
				$data = $this->hierarchy->getHierarchy ($familyNodeId);
			}
			if (!$data) {
				$errorHtml = 'Error: ' . $this->hierarchy->getError ();
				return false;
			}
		}
		
		# Return the hierarchy
		return $data;
	}
	
	
	# Function to get the special dates
	private function getSpecialDates ()
	{
		# Get the list
		$data = $this->databaseConnection->select ($this->settings['database'], 'specialDates');
		
		# Regroup by date, retaining the flatness of the array
		$data = application::regroup ($data, 'date', false, true);
		
		# Return the data
		return $data;
	}
	
	
	# Function to get term labels
	private function getTermLabels ()
	{
		# Return an empty list if none
		if (!$this->settings['termLabels']) {return array ();}
		
		# Create the list
		$termLabels = explode (',', $this->settings['termLabels']);
		foreach ($termLabels as $index => $termLabel) {
			$termLabels[$index] = trim ($termLabel);
		}
		
		# Return the list
		return $termLabels;
	}
	
	
	# Function to calculate the details for a term, including its date range
	private function getTerm ($customYear, $termName)
	{
		# Get the terms
		$terms = $this->getTerms ();
		
		# Assemble the key
		$key = $customYear . '/' . $termName;
		
		# End if not found
		if (!isSet ($terms[$key])) {return false;}
		
		# Otherwise return the data
		return $terms[$key];
	}
	
	
	# Function to get the term dates
	private function getTerms ($startDate = false, $untilDate = false)
	{
		# If dates are supplied, use these as a filter to reduce fetching of data, by ensuring that the start date and end date are within the range
		$conditions = array ();
		if ($startDate && $untilDate) {
			$conditions = "(startDate >= '{$startDate}' AND startDate <= '{$untilDate}') OR (untilDate >= '{$startDate}' AND untilDate <= '{$untilDate}')";
		}
		
		# Get the list
		$data = $this->databaseConnection->select ($this->settings['database'], 'terms', $conditions, array (), true, $orderBy = 'startDate');
		
		# Reindex by indicator (e.g. '2019-20/michaelmas') and add the customYear in
		$terms = array ();
		foreach ($data as $id => $term) {
			$customYear = $term['startYear'] . '-' . substr ($term['endYear'], -2);
			$term['customYear'] = $customYear;
			$key = $customYear . '/' . $term['termLabel'];
			$terms[$key] = $term;
		}
		
		# Determine for each whether the current date is within the term
		#!# Consider setting the isCurrent period to extend until the day before the start of the next term rather than just its untilDate
		$today = date ('Y-m-d');
		foreach ($terms as $key => $term) {
			$isCurrent = (($today >= $term['startDate']) && ($today <= $term['untilDate']));
			$terms[$key]['isCurrent'] = $isCurrent;
		}
		
		# Return the data
		return $terms;
	}
	
	
	# Function to get the people having bookings
	private function getPeople ($limitToUserId = false, $formattedLinkedVersion = false, $highlightCurrent = false, $returnAsNameCompiled = false, $filterToCorrectlyAttached = false, $addConsolidationLinks = false, $recentYears = false)
	{
		# Determine the conditions
		$conditions = array ();
		if ($limitToUserId) {$conditions['id'] = $limitToUserId;}
		
		# In recent people mode, get people who have a booking within the number of years specified, e.g. last year
		if ($recentYears) {
			$query = "
				SELECT
				people.*
				FROM people
				INNER JOIN (
					SELECT DISTINCT		/* Distinct reduces the query from 1.2s to 0.2s */
						bookedForUserid
					FROM bookings
					WHERE `date` > DATE(NOW()-INTERVAL {$recentYears} YEAR)
				) AS bookings
					ON bookings.bookedForUserid LIKE CONCAT('%|', people.id, '|%')
				GROUP BY people.id
				ORDER BY surname,forename,id
			;";
			if (!$data = $this->databaseConnection->getData ($query, "{$this->settings['database']}.people")) {return array ();}
			
		# Otherwise, get all
		} else {
			if (!$data = $this->databaseConnection->select ($this->settings['database'], 'people', $conditions, array (), true, 'surname,forename,id')) {return array ();}
		}
		
		# Format if required
		$list = array ();
		foreach ($data as $userId => $person) {
			$nameComponents = array ();
			$hasName = ($person['name'] && ($person['name'] != $userId));
			if ($hasName) {
				$nameComponents[] = trim ($person['name']);
			}
			$isRealUsername = $this->isRealUsername ($userId);
			if ($isRealUsername) {
				$nameComponents[] = "<{$userId}>";
			}
			if (!$nameComponents) {
				$nameComponents[] = trim ($person['name']);
			}
			$name = trim (implode (' ', $nameComponents));
			$data[$userId]['nameCompiled'] = $name;
			$name = htmlspecialchars ($name);
			if ($highlightCurrent) {
				if ($userId == $this->user) {$name = "<strong>{$name}</strong>";}
			}
			$list[$userId] = "<a href=\"{$this->baseUrl}/people/" . htmlspecialchars (urlencode ($userId)) . '/">' . $name . '</a>';
			
			# Add a link to consolidate the data if required
			if ($addConsolidationLinks) {
				if (!$isRealUsername || !$hasName) {
					$list[$userId] .= " <a href=\"{$this->baseUrl}/people/consolidate/" . htmlspecialchars (urlencode ($userId)) . "/\" class=\"consolidate\">[Consolidate &hellip;]</a>";
					$list[$userId] .= " <a href=\"{$this->baseUrl}/bookings/search.html?bookedForUserid=" . htmlspecialchars (urlencode ($userId)) . "\" class=\"consolidate\">[Bookings]</a>";
					
				}
			}
			
			# Filter to people correctly attached to a username if required
			if ($filterToCorrectlyAttached) {
				$isCorrectlyAttached = ($isRealUsername && $hasName);
				if (!$isCorrectlyAttached) {
					unset ($data[$userId]);
					unset ($list[$userId]);
				}
			}
		}
		
		# Supply as name compiled if required
		if ($returnAsNameCompiled) {
			foreach ($data as $userId => $person) {
				$data[$userId] = $person['nameCompiled'];
			}
		}
		
		# If limiting to a user ID, just get the data for that key - if a string is supplied, this means obtaining a person
		if (is_string ($limitToUserId)) {
			$data = $data[$limitToUserId];
		}
		
		# Convert to list if required
		if ($formattedLinkedVersion) {
			$data = $list;
		}
		
		# Return the data
		return $data;
	}
	
	
	# Function to enable consolidation of a user string to another user
	public function consolidate ($username = false)
	{
		# Start the HTML
		$html = '';
		
		# Get all people
		$people = $this->getPeople (false, false, false, $returnAsNameCompiled = true);
		
		# End if a person has been selected but they are not in the list
		if ($username) {
			if (!isSet ($people[$username])) {
				$html = "<p>The selected person, <em>" . htmlspecialchars ($username)  . "</em> does not appear to be in the list of <a href=\"{$this->baseUrl}/people/\">people</a>. Please check the URL and try again.</p>";
				echo $html;
				return false;
			}
		}
		
		# Get the people correctly attached to usernames
		$data = $this->getPeople (false, false, false, $returnAsNameCompiled = false, $filterToCorrectlyAttached = true);
		$peopleCorrectlyAttached = array ();
		foreach ($data as $userId => $person) {
			$peopleCorrectlyAttached[$userId] = $userId . ' - ' . $person['name'];
		}
		
		# Create the form
		$form = new form (array (
			'displayRestrictions' => false,
			'autofocus' => true,
			'formCompleteText' => false,
		));
		$form->select (array (
			'name' => 'from',
			'title' => 'Assign this user',
			'values' => $people,
			'default' => $username,
			'editable' => (!$username),
			'required' => true,
		));
		$form->select (array (
			'name' => 'to',
			'title' => 'To entries for this user',
			'values' => $peopleCorrectlyAttached,
			'default' => false,
			'required' => true,
		));
		$form->validation ('different', array ('from', 'to'));
		$form->checkboxes (array (
			'name'		=> 'confirmation',
			'title'		=> 'Confirmation',
			'values'	=> array ('I have double-checked the above.'),
			'required'	=> 1,
		));
		
		# Process the form
		if ($result = $form->process ($html)) {
			
			# Update the data - bookings.bookedForUserid
			$preparedStatementValues = array (
				'from' => '|' . $result['from'] . '|',
				'to'   => '|' . $result['to']   . '|',
			);
			$query = "UPDATE {$this->settings['database']}.bookings SET bookedForUserid = REPLACE(bookedForUserid, :from, :to);";
			if (!$this->databaseConnection->query ($query, $preparedStatementValues)) {
				application::dumpData ($this->databaseConnection->error ());
			}
			
			# Update the data - areaOfActivity.people
			$query = "UPDATE {$this->settings['database']}.areaOfActivity SET people = REPLACE(people, :from, :to);";
			if (!$this->databaseConnection->query ($query, $preparedStatementValues)) {
				application::dumpData ($this->databaseConnection->error ());
			}
			
			# Repopulate the people table
			$this->repopulatePeopleTable ();
			
			# Confirm success
			$html  = "\n<p>{$this->tick} The entry for <em>" . htmlspecialchars ($username)  . "</em> has been consolidated into <em>" . htmlspecialchars ($peopleCorrectlyAttached[$result['to']]) . '</em>.</p>';
			$html .= "\n<p>Thank you for helping keep the database tidy.</p>";
			$html .= "\n<p><a href=\"{$this->baseUrl}/people/\">Consolidate another entry?</a></p>";
		}
		
		# Show the HTML
		echo $html;
	}
	
	
	# Function to get the list of rooms, optionally limited to a specified ID (not moniker)
	private function getRooms ($id = false, $includeNote = false, $splitByInternalExternal = true, $indexById = false)
	{
		# Ensure that, if an ID is supplied, it is numeric
		if ($id !== false && !ctype_digit ($id)) {return false;}
		
		# Get the rooms
		$query = "SELECT
				rooms.id,
				rooms.moniker,
				rooms.name AS roomName,
				rooms.note AS roomNote,
				buildings.name as buildingName,
				CONCAT_WS(', ',rooms.name,buildings.name) AS name,
				" . ($splitByInternalExternal ? "IF(buildings.isInternal=1,'Internal','External')" : "'-'" /* Same string (dash) to be used below */) . " AS isInternal
			FROM rooms
			LEFT JOIN buildings ON rooms.buildingId = buildings.id
			" . ($id ? " WHERE rooms.id = " . $this->databaseConnection->quote ($id) : '') . "
			ORDER BY
				isInternal DESC /* i.e. Internal before External */,
				buildingName,
				roomName
		;";
		$databaseFunction = ($id ? 'getOne' : 'getData');
		$data = $this->databaseConnection->{$databaseFunction} ($query, "{$this->settings['database']}.rooms");
		
		#!# Hierarchy needs natsorting
		
		# Do regrouping if fetching all data
		if (!$id) {
			
			# Use the moniker as the ID when in listing mode
			if (!$indexById) {
				$roomsByMoniker = array ();
				foreach ($data as $id => $room) {
					$moniker = $room['moniker'];
					$roomsByMoniker[$moniker] = $room;
				}
				$data = $roomsByMoniker;
			}
			
			# Regroup by type
			$data = application::regroup ($data, 'isInternal');
			
			# Regroup each type by building
			foreach ($data as $type => $buildings) {
				$data[$type] = application::regroup ($buildings, 'buildingName');
			}
			
			# Flatten the rooms structure as simple key=>value
			foreach ($data as $type => $buildings) {
				foreach ($buildings as $building => $rooms) {
					foreach ($rooms as $key /* moniker or id */ => $attributes) {
						$data[$type][$building][$key] = $attributes['roomName'];		// Shorter version of the room name, since the context is already in the hierarchy
						if ($includeNote) {
							if ($attributes['roomNote']) {
								$data[$type][$building][$key] .= ' - *' . $attributes['roomNote'] . '*';
							}
						}
					}
				}
			}
		}
		
		# If not splitting by internal/external, reduce one level of the hierarchy
		if (!$splitByInternalExternal) {
			$data = $data['-'];
		}
		
		# Return the data
		return $data;
	}
	
	
	# Function to clone entries as a batch
	public function clone ()
	{
		# Start the HTML
		$html = '';
		
		# Get the activities hierarchy
		if (!$this->activitiesHierarchy = $this->getActivities (false, false, true, true, $errorHtml)) {
			$html .= $errorHtml;
			echo $html;
			return false;
		}
		
		# Introduce the page
		$html .= "\n<p>In this section, you can mass-copy bookings in an area of activity from one time period to another.</p>";
		$html .= "\n<p>After initially completing the form, you will be shown the new proposed bookings, and then asked to confirm their creation.</p>";
		
		# Prevent double-submission of the form; see: https://stackoverflow.com/a/4473801/180733
		#!# Move into ultimateForm
		$html .= "\n<script>
			$(document).ready (function (){
				$('form').submit(function(){
					var myForm = $(this);
					if (myForm.data ('submitted') === true) {
						e.preventDefault ();
					} else {
						myForm.data ('submitted', true);
					}
				});
			});
		</script>
		";
		
		# Start the form
		$form = new form (array (
			'div' => 'lines vertical',
			'displayRestrictions' => false,
			'autofocus' => true,
			'formCompleteText' => false,
			'reappear' => true,
			'requiredFieldIndicator' => false,
			'picker' => true,
		));
		
		# Source bookings
		$form->heading (3, 'Copy from... (source bookings)');
		$form->select (array (
			'name' => 'activityId',
			'title' => 'Area of activity',
			'values' => hierarchy::asIndentedListing ($this->activitiesHierarchy),
			'required' => true,
			//'multiple' => true,
			//'size' => 8,
		));
		$form->datetime (array (
		    'name'		=> 'startdate',
		    'title'		=> 'From start date',
		    'level'		=> 'date',
			'required'	=> true,
		));
		$form->datetime (array (
		    'name'		=> 'enddate',
		    'title'		=> 'Until last date',
		    'level'		=> 'date',
			'required'	=> true,
		));
		$this->checkStartEndDate ($form);
		
		# Target bookings
		$form->heading (3, 'Copy to... (target bookings)');
		$form->datetime (array (
		    'name'		=> 'shiftdate',
		    'title'		=> 'Start date becomes',
		    'level'		=> 'date',
			'required'	=> true,
		));
		$form->checkboxes (array (
			'name'		=> 'draft',
			'title'		=> 'Set as draft bookings?',
			'values'	=> array ('draft' => 'Yes, set as draft bookings'),
			'default'	=> array ('draft'),
			'output'	=> array ('processing' => 'special-setdatatype'),
		));
		
		# Confirm
		if ($unfinalisedData = $form->getUnfinalisedData ()) {
			if (!$form->getElementProblems ()) {
				$form->heading (3, 'Confirmation');
				$form->checkboxes (array (
					'name'		=> 'confirm',
					'title'		=> 'Confirm?',
					'values'	=> array ('confirm' => '<strong>Yes, create these new cloned bookings</strong>'),
					'entities'	=> false,
					'output'	=> array ('processing' => 'special-setdatatype'),
				));
			}
		}
		
		# Process the form
		if (!$result = $form->process ($html)) {
			echo $html;
			return false;
		}
		
		# Spacing
		$html .= "\n<br />";
		$html .= "\n<br />";
		$html .= "\n<hr />";
		
		# Get the bookings
		if (!$bookings = $this->getBookingsActivitiesHierarchyBetween ($result['activityId'], $result['startdate'], $result['enddate'])) {
			$html .= "\n<p><strong>No bookings matching those criteria were found.</strong></p>";
			echo $html;
			return;
		}
		
		# Determine the number of days to add; see: https://stackoverflow.com/a/16177475/180733
		$earlier = new DateTime ($result['startdate']);
		$later   = new DateTime ($result['shiftdate']);
		$days = $earlier->diff ($later)->format ('%r%a');	// %r adds - if negative
		
		# Determine the new dates; see: https://stackoverflow.com/a/3727639/180733
		$newDates = array ();
		foreach ($bookings as $id => $booking) {
			$sign = ($days >= 0 ? '+' : '');
			$newDates[$id] = date ('Y-m-d', strtotime ($booking['date'] . " {$sign}{$days} day"));
		}
		
		# Show differences in the listing, by adding a new column
		foreach ($bookings as $id => $booking) {
			$bookings[$id] = application::array_insert_value ($bookings[$id], 'New date', '<span class="warning"><strong>' . $newDates[$id] . '</strong></span>', $afterField = 'date');
			$bookings[$id]['draft'] = ($result['draft'] ? '<span class="warning"><strong>&#10003;</strong></span>' : '');
			$bookings[$id]['bookedByUserid'] = '<span class="warning"><strong>' . $this->user . '</strong></span>';
			$bookings[$id]['updatedByUserid'] = '';
		}
		
		# Show the bookings, with editing links disabled
		$totalBookings = count ($bookings);
		$html .= "\n<p><strong>The following shows the {$totalBookings} matching bookings, and the new date. To create the new bookings, tick the confirmation box above and resubmit the form.</strong></p>";
		$html .= "\n<br />";
		$html .= $this->dataListing ($bookings, 'bookings', array (), false, $editingLinks = false);
		
		# Get the fields in the booking tables, to use in the cloning SQL, and remove automatic fields
		$bookingsFields = $this->databaseConnection->getFieldnames ($this->settings['database'], 'bookings');
		$bookingsFields = array_diff ($bookingsFields, array ('id', 'createdAt'));
		
		# Determine the fields to clone, i.e. those not being adjusted manually in the query below
		$adjustedFields = array ('date', 'draft', 'series', 'bookedByUserid', 'updatedByUserid');
		$cloneFields = array_diff ($bookingsFields, $adjustedFields);
		$cloneFieldsImploded = implode (', ', $cloneFields);
		
		# Determine the booking IDs
		$bookingIds = array_keys ($bookings);
		$bookingIdsImploded = implode (', ', $bookingIds);
		
		# Series ID adjustment
		$seriesIdAdjustment = ($days * 24*60*60) * 1000000000;	// I.e. maintain last 9 digits
		
		# Do the insert if required
		if ($result['confirm']) {
			
			# Assemble the query
			# See createSeries for series number handling - seconds + 9-digit-microseconds
			# The inner and outer CAST for series avoids the addition becoming an exponent; see: https://dba.stackexchange.com/a/28045/83387
			$query = "
				INSERT
				INTO {$this->settings['database']}.bookings ({$cloneFieldsImploded}, date, series, draft, bookedByUserid, updatedByUserid)
					SELECT
						{$cloneFieldsImploded},
						DATE_ADD(`date`, INTERVAL {$days} DAY) AS `date`,
						IF(ISNULL(series), NULL, CAST(CAST((series + {$seriesIdAdjustment}) AS DECIMAL(30)) AS CHAR)) AS series,
						" . ($result['draft'] ? '1' : 'NULL') . " AS draft,
						'{$this->user}' AS bookedByUserid,
						NULL AS updatedByUserid
					FROM {$this->settings['database']}.bookings
					WHERE bookings.id IN({$bookingIdsImploded})
					ORDER BY date,startTime,id
			;";
			
			# Execute
			if (!$result = $this->databaseConnection->query ($query)) {
				application::dumpData ($this->databaseConnection->error ());
				$html = "\n<p class=\"warning\">A problem occured while trying to add the new bookings. Please contact the Webmaster.</p>";
				echo $html;
				return;
			}
			
			# Confirm success, resetting the content
			$html  = "\n<p>{$this->tick} The {$totalBookings} new bookings have been created.</p>";
			$html .= "\n<p><a href=\"{$this->baseUrl}/\">Browse the timetable page.</a></p>";
		}
		
		# Show the HTML
		echo $html;
	}
	
	
	# Function to get bookings for an activities hierarchy downwards between two dates
	private function getBookingsActivitiesHierarchyBetween ($activityId, $startDate, $endDate)
	{
		# Start a set of constraints
		$where = array ();
		$preparedStatementValues = array ();
		
		# Get the activity hierarchy from the selected activity downards (not ancestors upwards), and register this list as a constraint
		$activitiesFamilyIds = $this->getActivities (false, $activityId, $hideFromNew = false, $familyNodeIdIncludeAncestors = false);
		$activitiesFamilyIdsQuoted = array ();
		$i = 0;
		$placeholders = array ();
		foreach ($activitiesFamilyIds as $activitiesFamilyId => $activity) {
			$placeholder = "activity{$i}";
			$preparedStatementValues[$placeholder] = $activitiesFamilyId;
			$placeholders[] = ':' . $placeholder;
			$i++;
		}
		$where[] = 'areaOfActivityId IN (' . implode (', ', $placeholders) . ')';
		
		# Add date constraints
		$where[] = 'date >= :startDate';
		$where[] = 'date <= :endDate';
		$preparedStatementValues['startDate'] = $startDate;
		$preparedStatementValues['endDate'] = $endDate;
		
		# Get the data
		$table = 'bookings';
		$query = "SELECT * FROM {$this->settings['database']}.{$table} WHERE " . implode (' AND ', $where) . ' ORDER BY date,startTime,id;';
		$data = $this->databaseConnection->getData ($query, "{$this->settings['database']}.{$table}", true, $preparedStatementValues);
		
		# Return the data
		return $data;
	}
	
	
	# Function to check the start and end date
	private function checkStartEndDate (&$form)
	{
		# Ensure both are completed if one is
		$form->validation ('all', array ('startdate', 'enddate'));
		
		# Take the unfinalised data to deal with start/end date comparisons
		if ($unfinalisedData = $form->getUnfinalisedData ()) {
			if ($unfinalisedData['Startdate'] && $unfinalisedData['Enddate']) {
				
				# Assemble the start & end dates as a number (this would normally be done in ultimateForm in the post-unfinalised data processing section
				$startDate = (int) str_replace ('-', '', $unfinalisedData['Startdate']);
				$endDate = (int) str_replace ('-', '', $unfinalisedData['Enddate']);
				
				# Check that the start (and thereby the end date) are after the current date
				if ($startDate < date ('Ymd')) {
					$form->registerProblem ('datefuture', 'The start/end dates cannot be retrospective. Please go back and correct this.');
				} else {
					
					# Check that the start date comes before the end date; NB the >= seems to work successfully with comparison of strings including the dash (-) character
					if ($startDate > $endDate) {
						$form->registerProblem ('datemismatch', 'The end date must be on or after the start date. Please go back and correct this.');
					}
				}
			}
		}
		
		# No return value as the form is passed as a handle
		return;
	}
	
	
	# Data maintenance function
	#!# Purpose of this needs to be clarified
	public function maintenance ()
	{
		# Start the HTML
		$html = '';
		
		# Button to start
		if (!isSet ($_POST['go'])) {
			$html .= "\n<p>This page will perform data maintenance. It is not necessary to do this but can be useful when wanting to keep the data tidy. It can be safely re-run.</p>";
			$html .= "\n<form action=\"{$this->baseUrl}/maintenance.html\" method=\"post\"><input type=\"submit\" value=\"Click to start maintenance\" /><input type=\"hidden\" name=\"go\" /></form>";
			echo $html;
			return;
		}
		
		# Repopulate the people table
		$this->repopulatePeopleTable ();
		
		# Reset link
		$html .= "<p>All done. <a href=\"{$this->baseUrl}/maintenance.html\">Reset page.</a></p>";
		
		# Show the HTML
		echo $html;
	}
	
	
	# Function to repopulate the people table
	#!# Should all this be done anyway within updatePeopleNames() ?
	private function repopulatePeopleTable ()
	{
		# Repopulate the people table
		$usernames = $this->getUniqueUsernames ();
		$inserts = array ();
		foreach ($usernames as $username) {
			$inserts[] = array ('id' => $username, 'forename' => '', 'surname' => '', 'name' => '');
		}
		$this->databaseConnection->truncate ($this->settings['database'], 'people', true);
		#!# This can create "Duplicate entry 'Forename surname' for key 'PRIMARY'" if there is both "Forename Surname" (capitalised) and "Forename surname" (uncapitalised) present; this can be tidied using Consolidate user entries twice
		$this->databaseConnection->insertMany ($this->settings['database'], 'people', $inserts, false, false, $emptyToNull = false);
		$this->updatePeopleNames ();
	}
	
	
	# API call for dashboard
	public function apiCall_dashboard ($username = NULL)
	{
		# Start the HTML
		$html = '';
		
		# State that the service is enabled
		$data['enabled'] = true;
		
		# Ensure a username is supplied
		if (!$username) {
			$data['error'] = 'No username was supplied.';
			return $data;
		}
		
		# Define description
		$data['descriptionHtml'] = "<p>Timetables for all areas of the {$this->settings['institutionDescription']} are available, kept up-to-date in real time.</p>";
		
		# Add key links
		// $data['links']["{$this->baseUrl}/my/"] = '{icon:asterisk_orange} My timetable';
		if (isSet ($this->administrators[$username])) {
			$data['links']["{$this->baseUrl}/bookings/add.html"] = '{icon:add} Add a booking';
		}
		
		# Add section links
		$html .= "<ul>
			<li><a href=\"{$this->baseUrl}/activities/\">Areas of activity</a></li>
			<li><a href=\"{$this->baseUrl}/rooms/\">Rooms</a></li>
			<li><a href=\"{$this->baseUrl}/people/\">People</a></li>
		</ul>";
		
		# Register the HTML
		$data['html'] = $html;
		
		# Return the data
		return $data;
	}
}

?>
