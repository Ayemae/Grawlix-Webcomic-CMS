<?php

/* ! Class * * * * * * * */

class GrlxFirstRun {

	protected $action;
	protected $db;
	protected $dbInfo;
	protected $dbString;
	protected $user;
	public    $step;
	public    $sendTo;
	public    $message;
	public    $content;
	protected $instruction;
	protected $timezone;
	protected $timezoneSelect;
	protected $fields;
	protected $form;
	protected $strName;
	protected $error;
	protected $success;

	/**
	 * Setup and direct the action
	 */
	public function __construct() {
		$this->getArgs(func_get_args());
		switch ($this->action) {
			case 'precheck':
				$this->connectDB();
				$this->step = 'Step One';
				if ( $this->db ) {
					$already_installed = $this->checkExisting();
					if ( $already_installed === TRUE )
					{
						$this->step = 'Complete';
					}
				}
				$this->doStepOne();
				break;
			case '1':
				$this->doStepOne();
				break;
			case '2':
				$this->doStepTwo();
				break;
			case '3':
				$this->doStepThree();
				break;
		}
		$this->sendTo = $_SERVER['SCRIPT_NAME'];
		$this->formatMessage();
		$this->formatContent();
	}

	/**
	 * Pass in any arguments
	 *
	 * @param array $list - arguments from main script
	 */
	protected function getArgs($list=null) {
		$list = $list[0];
		if ( isset($list) ) {
			foreach ( $list as $key=>$val ) {
				if ( property_exists($this, $key) ) {
					$this->{$key} = $val;
				}
			}
		}
	}

	/**
	 * Direct step one
	 */
	protected function doStepOne() {
		$this->step = 'Step One';
		$this->instruction = 'This script will install the Grawlix CMS. To start, enter some information about your MySQL database.';
//		$this->buildTimezoneSelect();
		$this->formOne();
		$this->form = $this->buildFormFields(false);
	}

	/**
	 * Direct step two
	 */
	protected function doStepTwo() {
		if ( $this->dbInfo ) {
			foreach ( $this->dbInfo as $key=>$val ) {
				$this->cleanText($val);
				$this->dbInfo[$key] = $val;
			}
		}
		$this->connectDB();
		if ( $this->db ) {
			$this->formOne();
			$form1  = $this->buildFormFields(true);
			$form1 .= '<input type="hidden" name="timezone" value="'.$this->timezone.'" />';
			$this->step = 'Step Two';
			$this->instruction = 'Now, enter some user information so you can log in to the admin panel.';
			$this->formTwo();
			$form2 = $this->buildFormFields(false);
			$this->form = $form2.$form1;
		}
		else {
			$this->error = 'Couldn’t connect to the database. Check your information and try again.';
			$this->doStepOne();
		}
	}

	/**
	 * Direct step three
	 */
	protected function doStepThree() {
		if ( $this->user ) {
			foreach ( $this->user as $key=>$val ) {
				$this->cleanText($val);
				$this->user[$key] = $val;
			}
		}
		$this->user['password'] = $this->hashPassword($this->user['password']);
		if ( $this->user['password'] === null ) {
			$this->error = 'I couldn’t create a hash of your password.';
		}
		else {
			$this->dbData();
			$this->connectDB();
		}
		if ( $this->db && $this->dbString ) {
			$populate = mysqli_multi_query($this->db,$this->dbString);
		}
		if ( $populate ) {
			$dir_problem_list = $this->makeAssetDirs();
			if ( is_array($dir_problem_list))
			{
				$this->error  = 'Couldn’t create certain directories:';
			}
		}
		else {
			$this->error = 'Couldn’t populate the database.';
		}
		if ( !is_array($dir_problem_list) ) {

			$this->step = 'Complete';
			$this->success = 'The Grawlix CMS is almost installed!';
		}
		else {
			$this->error = 'Couldn’t create asset directories.';
			if ( $dir_problem_list )
			{
				foreach ( $dir_problem_list as $key => $val )
				{
					$this->error .= '<p>'.$val.'</p>';
				}
			}
		}
	}

	/**
	 * Establish db connection
	 */
	protected function connectDB() {
		$info = $this->dbInfo;
		@$db = new mysqli($info['db_host'],$info['db_user'],$info['db_pswd'],$info['db_name']);
		@$db->set_charset("utf8");
		if ( $db && ( !$db->connect_errno || $db->connect_errno == 0 ) ) {
			$this->db = $db;
		}
	}

	/**
	 * Check for any existing users in db
	 */
	protected function checkExisting() {
		$result = $this->db->query("SELECT level FROM grlx_user WHERE level > 1");
		if ( $result->num_rows > 0 ) {
			$this->error = 'The Grawlix CMS is already installed.';
			return TRUE;
		}
	}

	/**
	 * Clean submitted text
	 */
	protected function cleanText(&$str) {
		$text = trim($str);
		$text = strip_tags($text);
		$text = preg_replace('/\s\s+/',' ',$text); // strip excess whitespace
		$text = mb_substr($text,0,1000);
		$text = str_replace(';','',$text); // No semicolons makes for injection-free MySQL statements.
		$text = str_replace("'","&#8217;",$text);
		$str = $text;
	}

	/**
	 * Hash user password
	 */
	protected function hashPassword($admin_pass='') {
		$hash = '$2y$04$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG';
		$test = crypt("password",$hash);
		$pass = $test == $hash;
		if ( $pass && ($admin_pass != '') ) {
			$admin_hash = password_hash($admin_pass,PASSWORD_BCRYPT);
			if ( password_verify($admin_pass,$admin_hash) ) {
				return $admin_hash;
			}
		}
		return null;
	}

	/**
	 * Format message for display in main script
	 */
	protected function formatMessage() {
		unset($this->message);
		if ( $this->error ) {
			$this->message = '<div class="message exclaim"><i></i>'.$this->error.'</div>';
		}
		if ( $this->success ) {
			$this->message = '<div class="message select"><i></i>'.$this->success.'</div>';
		}
	}

	/**
	 * Format the form and other output for display in main script
	 */
	protected function formatContent() {
		if ( $this->instruction && !$this->error ) {
			$output = '<p>'.$this->instruction.'</p>';
		}
		if ( $this->error ) {
			@$output .= '<p>Need help? <a href="mailto:grawlixcomix@gmail.com">Contact support</a>.</p>';
		}
		if ( $this->form ) {
			$output .= $this->form;
			if ( $this->timezoneSelect ) {
//				$output .= $this->timezoneSelect;
			}
			$output .= '<button class="btn primary next" name="submit" type="submit" value="next"><i></i>Next</button>';
		}
		if ( $this->success ) {
			$configOutput  = "&lt;?php\n";
			$configOutput .= '$setup[\'db_host\'] = \''.$this->dbInfo['db_host'].'\';'."\n";
			$configOutput .= '$setup[\'db_user\'] = \''.$this->dbInfo['db_user'].'\';'."\n";
			$configOutput .= '$setup[\'db_pswd\'] = \''.$this->dbInfo['db_pswd'].'\';'."\n";
			$configOutput .= '$setup[\'db_name\'] = \''.$this->dbInfo['db_name'].'\';'."\n";
			if ( is_file('config.php'))
			{
				@$output .= '<p>One last step: Paste the code below into your <strong>config.php</strong> file.</p>';
			}
			else
			{
				@$output .= '<p>One last step: Create a file called <strong>config.php</strong> in the same folder as firstrun.php. Then paste the code below into that file.</p>';
			}
			$output .= '<textarea cols="60" rows="6" id="config_file_data">'.$configOutput.'</textarea>';
			$output .= '<p>Once that’s done you can access your <a href="_admin/panl.login.php">Grawlix CMS admin panel</a>. You should also <strong>delete firstrun.php</strong> from your main folder and <strong>_upgrade-to-1.3.php</strong> in your _admin folder.</p>';
		}
		$this->content = $output;
	}

	/**
	 * Build array and select form element
	 */
	protected function buildTimezoneSelect() {
		$list['America/Anchorage'] = 'UTC−09:00: Anchorage';
		$list['America/Los_Angeles'] = 'UTC−08:00: Los Angeles, Vancouver, Tijuana';
		$list['America/Denver'] = 'UTC−07:00: Denver, Phoenix, Calgary, Ciudad Juárez';
		$list['America/Chicago'] = 'UTC−06:00: Chicago, Guatemala City, Mexico City, San José, San Salvador, Tegucigalpa, Winnipeg';
		$list['America/New_York'] = 'UTC−05:00: New York, Lima, Toronto, Bogotá, Havana, Kingston';
		$list['America/Caracas'] = 'UTC−04:30: Caracas';
		$list['America/Santiago'] = 'UTC−04:00: Santiago, La Paz, San Juan de Puerto Rico, Manaus, Halifax';
		$list['America/St_Johns'] = 'UTC−03:30: St. John’s';
		$list['America/Argentina/Buenos_Aires'] = 'UTC−03:00: Buenos Aires, Montevideo, São Paulo';
		$list['Europe/Lisbon'] = 'UTC±00:00: Accra, Abidjan, Casablanca, Dakar, Dublin, Lisbon, London';
		$list['Europe/Berlin'] = 'UTC+01:00: Belgrade, Berlin, Brussels, Lagos, Madrid, Paris, Rome, Tunis, Vienna, Warsaw';
		$list['Europe/Istanbul'] = 'UTC+02:00: Athens, Sofia, Cairo, Kiev, Istanbul, Beirut, Helsinki, Jerusalem, Johannesburg, Bucharest';
		$list['Europe/Moscow'] = 'UTC+03:00: Moscow, Nairobi, Baghdad, Doha, Khartoum, Minsk, Riyadh';
		$list['Europe/Samara'] = 'UTC+04:00: Baku, Dubai, Samara, Muscat';
		$list['Asia/Karachi'] = 'UTC+05:00: Karachi, Tashkent, Yekaterinburg';
		$list['Asia/Kathmandu'] = 'UTC+05:45, Kathmandu';
		$list['Asia/Almaty'] = 'UTC+06:00: Almaty, Dhaka, Novosibirsk';
		$list['Asia/Jakarta'] = 'UTC+07:00: Jakarta, Bangkok, Krasnoyarsk, Hanoi';
		$list['Australia/Perth'] = 'UTC+08:00: Perth, Beijing, Manila, Singapore, Kuala Lumpur, Denpasar, Irkutsk';
		$list['Asia/Tokyo'] = 'UTC+09:00: Seoul, Tokyo, Pyongyang, Ambon, Yakutsk';
		$list['Australia/Adelaide'] = 'UTC+09:30, Adelaide';
		$list['Australia/Canberra'] = 'UTC+10:00: Canberra, Vladivostok, Port Moresby';
		$list['Pacific/Noumea'] = 'UTC+11:00: Honiara, Noumea';
		$list['Pacific/Auckland'] = 'UTC+12:00: Auckland, Suva';
		$list['Pacific/Honolulu'] = 'UTC−10:00: Papeete, Honolulu';
		$list['Pacific/Samoa'] = 'UTC−11:00: American Samoa';

		$output  = '<label for="timezone">Timezone</label>';
		$output .= '<select id="timezone" name="timezone">';
		foreach ( $list as $key=>$val ) {
			$output .= '<option value="'.$key.'">'.$val.'</option>';
		}
		$output .= '</select>';
		$this->timezoneSelect = $output;
	}

	/**
	 * Build the form contents
	 *
	 * @return string $output - html for form fields
	 */
	protected function buildFormFields($hidden=false) {
		if ( $this->fields ) {
			foreach ( $this->fields as $info ) {
				if ( $hidden ) {
					$name = $this->strName.'['.$info['name'].']';
					@$output .= '<input type="hidden" name="'.$name.'" value="'.$info['value'].'" />';
				}
				else {
					if ( $info['name'] == 'email' ) {
						$req = 'required pattern="email"';
						$err = 'Not a valid address';
					}
					else {
						$req = 'required';
						$err = 'Required field';
					}
					$name = $this->strName.'['.$info['name'].']';
					@$output .= '<div>';
					$output .= '<label for="'.$name.'">'.$info['label'].'</label>';
					$output .= '<input type="text" '.$req.' id="'.$name.'" name="'.$name.'" maxlength="255" value="'.$info['value'].'" />';
					$output .= '<small class="error">'.$err.'</small>';
					$output .= '</div>';
				}
			}
		}
		return $output;
	}

	/**
	 * Form that gathers db info and credentials
	 */
	protected function formOne() {
		unset($this->fields);
		$this->strName = 'dbCred';
		$this->fields[] = array(
								'name'  => 'db_host',
								'label' => 'Host name',
								'value' => $this->dbInfo['db_host']
								);
		$this->fields[] = array(
								'name'  => 'db_name',
								'label' => 'Database name',
								'value' => $this->dbInfo['db_name']
								);
		$this->fields[] = array(
								'name'  => 'db_user',
								'label' => 'User name',
								'value' => $this->dbInfo['db_user']
								);
		$this->fields[] = array(
								'name'  => 'db_pswd',
								'label' => 'Password',
								'value' => $this->dbInfo['db_pswd']
								);
	}

	/**
	 * Form that gathers user info
	 */
	protected function formTwo() {
		unset($this->fields);
		$this->strName = 'user';
		$this->fields[] = array(
									'name'  => 'artist_name',
									'label' => 'Your name',
									'value' => $this->user['artist_name']
								);
		$this->fields[] = array(
									'name'  => 'email',
									'label' => 'Your email',
									'value' => $this->user['email']
								);
		$this->fields[] = array(
									'name'  => 'username',
									'label' => 'Login username',
									'value' => $this->user['username']
								);
		$this->fields[] = array(
									'name'  => 'password',
									'label' => 'Password',
									'value' => $this->user['password']
								);
	}

	/**
	 * Load the create and insert statements into a string
	 */
	protected function dbData() {
		$user = $this->user;
		$name = $this->dbInfo['db_name'];
		$year = date('Y');
		$timezone = $this->timezone;
		$site_root_directory = substr($_SERVER['REQUEST_URI'],0,-13);
		$site_root_directory ? $site_root_directory : $site_root_directory = '/';

		$str = <<<EOL

# UTF-8 all the things
# ------------------------------------------------------------

ALTER DATABASE $name
	CHARACTER SET utf8
	DEFAULT CHARACTER SET utf8
	COLLATE utf8_unicode_ci
	DEFAULT COLLATE utf8_unicode_ci
	;


# Dump of table grlx_ad_reference
# ------------------------------------------------------------

CREATE TABLE `grlx_ad_reference` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `source_id` tinyint(3) unsigned DEFAULT NULL,
  `source_rel_id` int(11) unsigned DEFAULT NULL,
  `small_width` smallint(5) unsigned DEFAULT NULL,
  `small_height` smallint(5) unsigned DEFAULT NULL,
  `small_image_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `medium_width` smallint(5) unsigned DEFAULT NULL,
  `medium_height` smallint(5) unsigned DEFAULT NULL,
  `medium_image_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `large_width` smallint(5) unsigned DEFAULT NULL,
  `large_height` smallint(5) unsigned DEFAULT NULL,
  `large_image_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tap_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `code` text COLLATE utf8_unicode_ci,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table grlx_ad_slot_match
# ------------------------------------------------------------

DROP TABLE IF EXISTS `grlx_ad_slot_match`;

CREATE TABLE `grlx_ad_slot_match` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ad_reference_id` int(11) unsigned DEFAULT NULL,
  `slot_id` int(11) unsigned DEFAULT NULL,
  `priority` tinyint(1) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


# Dump of table grlx_ad_source
# ------------------------------------------------------------

DROP TABLE IF EXISTS `grlx_ad_source`;

CREATE TABLE `grlx_ad_source` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `grlx_ad_source` WRITE;
/*!40000 ALTER TABLE `grlx_ad_source` DISABLE KEYS */;

INSERT INTO `grlx_ad_source` (`id`, `title`)
VALUES
	(1,'Custom'),
	(2,'Project Wonderful'),
	(3,'Google Adwords');

/*!40000 ALTER TABLE `grlx_ad_source` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table grlx_book
# ------------------------------------------------------------

DROP TABLE IF EXISTS `grlx_book`;

CREATE TABLE `grlx_book` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(64) COLLATE utf8_unicode_ci DEFAULT '',
  `description` text COLLATE utf8_unicode_ci,
  `tone_id` int(11) unsigned DEFAULT NULL,
  `sort_order` int(4) unsigned DEFAULT NULL,
  `options` text COLLATE utf8_unicode_ci,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `publish_frequency` varchar(12) COLLATE utf8_unicode_ci DEFAULT NULL,
  `date_start` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `grlx_book` WRITE;
/*!40000 ALTER TABLE `grlx_book` DISABLE KEYS */;

INSERT INTO `grlx_book` (`id`, `title`, `description`, `tone_id`, `sort_order`, `options`, `date_created`, `date_modified`, `publish_frequency`, `date_start`)
VALUES
	(1, 'My Comic', 'What’s this comic about? This is the place to put your comic’s elevator pitch.', NULL, 1, '<?xml version=\"1.0\" encoding=\"UTF-8\"?><book version=\"1.1\"><archive><behavior>single</behavior><page><layout>grid</layout><option>title</option><option>number</option><option>date</option></page><chapter><layout>list</layout><option>title</option><option>number</option></chapter></archive><rss><option>title</option><option>number</option></rss></book>', NOW(), NULL, NULL, NULL);

/*!40000 ALTER TABLE `grlx_book` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table grlx_book_page
# ------------------------------------------------------------

DROP TABLE IF EXISTS `grlx_book_page`;

CREATE TABLE `grlx_book_page` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `book_id` int(11) unsigned DEFAULT NULL,
  `marker_id` int(11) unsigned DEFAULT NULL,
  `tone_id` int(11) unsigned DEFAULT NULL,
  `sort_order` decimal(9,4) unsigned DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `date_publish` datetime DEFAULT NULL,
  `options` text COLLATE utf8_unicode_ci,
  `description` text COLLATE utf8_unicode_ci,
  `blog_title` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `blog_post` text COLLATE utf8_unicode_ci,
  `transcript` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `grlx_book_page` WRITE;
/*!40000 ALTER TABLE `grlx_book_page` DISABLE KEYS */;

INSERT INTO `grlx_book_page` (`id`, `title`, `book_id`, `marker_id`, `tone_id`, `sort_order`, `date_created`, `date_modified`, `date_publish`, `options`, `description`, `blog_title`, `blog_post`, `transcript`)
VALUES
	(1,'The First Page',1,1,NULL,1.0000,NOW(),NOW(),NOW(),NULL,'A quick synopsis of page one. ','Here’s a post','Add a blog post under your comic.','Add a transcript of your comic page.');

/*!40000 ALTER TABLE `grlx_book_page` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table grlx_image_match
# ------------------------------------------------------------

DROP TABLE IF EXISTS `grlx_image_match`;

CREATE TABLE `grlx_image_match` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `image_reference_id` int(11) unsigned DEFAULT NULL,
  `rel_id` int(11) unsigned DEFAULT NULL,
  `rel_type` enum('book','page','marker','static') COLLATE utf8_unicode_ci DEFAULT NULL,
  `sort_order` int(4) unsigned DEFAULT '1',
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `grlx_image_match` WRITE;
/*!40000 ALTER TABLE `grlx_image_match` DISABLE KEYS */;

INSERT INTO `grlx_image_match` (`id`, `image_reference_id`, `rel_id`, `rel_type`, `sort_order`, `date_created`, `date_modified`)
VALUES
	(1,1,1,'page',1,NOW(),NULL);

/*!40000 ALTER TABLE `grlx_image_match` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table grlx_image_reference
# ------------------------------------------------------------

DROP TABLE IF EXISTS `grlx_image_reference`;

CREATE TABLE `grlx_image_reference` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `grlx_image_reference` WRITE;
/*!40000 ALTER TABLE `grlx_image_reference` DISABLE KEYS */;

INSERT INTO `grlx_image_reference` (`id`, `url`, `description`, `date_created`, `date_modified`)
VALUES
	(1,'http://placehold.it/1000x400/7359ae/ddd.png&text=comic','placeholder comic image',NOW(),NULL);

/*!40000 ALTER TABLE `grlx_image_reference` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table grlx_image_tone_match
# ------------------------------------------------------------

DROP TABLE IF EXISTS `grlx_image_tone_match`;

CREATE TABLE `grlx_image_tone_match` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `image_reference_id` int(11) unsigned DEFAULT NULL,
  `tone_id` int(11) unsigned DEFAULT NULL,
  `slot_id` int(11) unsigned DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


# Dump of table grlx_link_list
# ------------------------------------------------------------

DROP TABLE IF EXISTS `grlx_link_list`;

CREATE TABLE `grlx_link_list` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sort_order` int(4) unsigned DEFAULT NULL,
  `img_path` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `group_id` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `grlx_link_list` WRITE;
/*!40000 ALTER TABLE `grlx_link_list` DISABLE KEYS */;

INSERT INTO `grlx_link_list` (`id`, `title`, `url`, `sort_order`, `img_path`, `group_id`)
VALUES
	(1,'Grawlix — The CMS for Comics','http://www.getgrawlix.com',1,NULL,NULL);

/*!40000 ALTER TABLE `grlx_link_list` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table grlx_marker
# ------------------------------------------------------------

DROP TABLE IF EXISTS `grlx_marker`;

CREATE TABLE `grlx_marker` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `marker_type_id` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `grlx_marker` WRITE;
/*!40000 ALTER TABLE `grlx_marker` DISABLE KEYS */;

INSERT INTO `grlx_marker` (`id`, `title`, `description`, `marker_type_id`)
VALUES
	(1,'The First Chapter','A quick synopsis of chapter one. ',1);

/*!40000 ALTER TABLE `grlx_marker` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table grlx_marker_type
# ------------------------------------------------------------

DROP TABLE IF EXISTS `grlx_marker_type`;

CREATE TABLE `grlx_marker_type` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rank` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `grlx_marker_type` WRITE;
/*!40000 ALTER TABLE `grlx_marker_type` DISABLE KEYS */;

INSERT INTO `grlx_marker_type` (`id`, `title`, `rank`)
VALUES
	(1,'Chapter',1),
	(2,'Scene',2);

/*!40000 ALTER TABLE `grlx_marker_type` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table grlx_milieu
# ------------------------------------------------------------

DROP TABLE IF EXISTS `grlx_milieu`;

CREATE TABLE `grlx_milieu` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `label` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `milieu_type_id` tinyint(3) unsigned DEFAULT NULL,
  `data_type` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sort_order` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `grlx_milieu` WRITE;
/*!40000 ALTER TABLE `grlx_milieu` DISABLE KEYS */;

INSERT INTO `grlx_milieu` (`id`, `title`, `description`, `label`, `value`, `milieu_type_id`, `data_type`, `sort_order`)
VALUES
	(1,'Site name',NULL,'site_name','My Comic',1,'title',1),
	(2,'Artist’s name',NULL,'artist_name','$user[artist_name]',1,'text',3),
	(3,'Copyright year(s)',NULL,'copyright','©$year',1,'text',4),
	(4,'Timezone',NULL,'timezone','$timezone',1,NULL,6),
	(5,'Site root directory','If you wish to run the Grawlix CMS in a subdirectory, enter its name here. Otherwise leave this blank.','directory','$site_root_directory',1,'path',2),
	(6,'Meta description',NULL,'meta_description','Enter a short blurb for your meta-description tag.',1,'text',5),
	(7,'Date format',NULL,'date_format','F j, Y',1,NULL,7),
	(8,'Permalink format',NULL,'permalink_format','/slug/sort',1,NULL,0),
	(9,'Default tone','When multi-tone is off (0), this is the tone ID for the site.','tone_id','1',2,NULL,NULL),
	(10,'Multi-tone switch','Turned on (1), the user can set a tone to individual items like comic and static pages.','multi_tone','0',2,NULL,NULL),
	(11,'Version','','db_version','1.0',4,NULL,NULL);

/*!40000 ALTER TABLE `grlx_milieu` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table grlx_milieu_type
# ------------------------------------------------------------

DROP TABLE IF EXISTS `grlx_milieu_type`;

CREATE TABLE `grlx_milieu_type` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sort_order` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `grlx_milieu_type` WRITE;
/*!40000 ALTER TABLE `grlx_milieu_type` DISABLE KEYS */;

INSERT INTO `grlx_milieu_type` (`id`, `title`, `sort_order`)
VALUES
	(1,'general',1),
	(2,'themes',NULL),
	(3,'custom',2),
	(4,'internal',NULL);

/*!40000 ALTER TABLE `grlx_milieu_type` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table grlx_path
# ------------------------------------------------------------

DROP TABLE IF EXISTS `grlx_path`;

CREATE TABLE `grlx_path` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rel_id` int(11) unsigned DEFAULT NULL,
  `rel_type` enum('static','book','archive','external') COLLATE utf8_unicode_ci DEFAULT NULL,
  `sort_order` tinyint(3) unsigned DEFAULT NULL,
  `in_menu` tinyint(1) unsigned DEFAULT '0',
  `edit_path` tinyint(1) unsigned DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `grlx_path` WRITE;
/*!40000 ALTER TABLE `grlx_path` DISABLE KEYS */;

INSERT INTO `grlx_path` (`id`, `title`, `url`, `rel_id`, `rel_type`, `sort_order`, `in_menu`, `edit_path`)
VALUES
	(1,'Home','/',1,'book',1,1,0),
	(2,'Error 404','/404',2,'static',0,0,0),
	(3,'My Comic','/comic',1,'book',2,1,1),
	(4,'Archive','/archive',3,'archive',3,1,1);

/*!40000 ALTER TABLE `grlx_path` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table grlx_static_page
# ------------------------------------------------------------

DROP TABLE IF EXISTS `grlx_static_page`;

CREATE TABLE `grlx_static_page` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` varchar(160) COLLATE utf8_unicode_ci DEFAULT NULL,
  `options` text COLLATE utf8_unicode_ci,
	`layout` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tone_id` int(11) unsigned DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `grlx_static_page` WRITE;
/*!40000 ALTER TABLE `grlx_static_page` DISABLE KEYS */;

INSERT INTO `grlx_static_page` (`id`, `title`, `description`, `options`, `tone_id`, `date_created`, `date_modified`)
VALUES
	(1, 'Home', 'Home Page', 'image-left', NULL, NOW(), NULL),
	(2, 'Error', 'Error 404', 'image-left', NULL, NOW(), NULL);

/*!40000 ALTER TABLE `grlx_static_page` ENABLE KEYS */;
UNLOCK TABLES;

# Dump of table grlx_static_content
# ------------------------------------------------------------

CREATE TABLE `grlx_static_content` (
  `id` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` int(9) NOT NULL DEFAULT '0',
  `sort_order` int(3) NOT NULL DEFAULT '0',
  `title` varchar(64) DEFAULT '',
  `url` varchar(160) DEFAULT '',
  `image` varchar(160) DEFAULT '',
  `content` text,
  `pattern` varchar(32) DEFAULT '',
  `created_on` datetime NOT NULL,
  `modified_on` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `page_id` (`page_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table grlx_tag
# ------------------------------------------------------------

DROP TABLE IF EXISTS `grlx_tag`;

CREATE TABLE `grlx_tag` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tag_type_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table grlx_tag_match
# ------------------------------------------------------------

DROP TABLE IF EXISTS `grlx_tag_match`;

CREATE TABLE `grlx_tag_match` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tag_id` int(11) unsigned DEFAULT NULL,
  `rel_type` enum('book_page','static_page') COLLATE utf8_unicode_ci DEFAULT NULL,
  `rel_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table grlx_tag_type
# ------------------------------------------------------------

DROP TABLE IF EXISTS `grlx_tag_type`;

CREATE TABLE `grlx_tag_type` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `color` varchar(6) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table grlx_theme_list
# ------------------------------------------------------------

DROP TABLE IF EXISTS `grlx_theme_list`;

CREATE TABLE `grlx_theme_list` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `label` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `directory` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `version` varchar(8) COLLATE utf8_unicode_ci DEFAULT NULL,
  `author` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `options` text COLLATE utf8_unicode_ci,
  `palette` text COLLATE utf8_unicode_ci,
  `date_created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `grlx_theme_list` WRITE;
/*!40000 ALTER TABLE `grlx_theme_list` DISABLE KEYS */;

INSERT INTO `grlx_theme_list` (`id`, `title`, `label`, `directory`, `description`, `version`, `author`, `url`, `options`, `palette`, `date_created`)
VALUES
	(1, 'Indotherm', 'indotherm', 'indotherm', 'A simple theme meant to be customized with your images and colors.', '1.0', 'The Grawlix Team', 'http://www.getgrawlix.com', NULL, NULL, NOW()),
	(2, 'Stick Figure', 'stick_figure', 'stick_figure', 'A simple theme meant to be customized with your images and colors.', '1.1', 'The Grawlix Team', 'http://www.getgrawlix.com', NULL, NULL, NOW());

/*!40000 ALTER TABLE `grlx_theme_list` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table grlx_theme_slot
# ------------------------------------------------------------

DROP TABLE IF EXISTS `grlx_theme_slot`;

CREATE TABLE `grlx_theme_slot` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `label` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `theme_id` int(11) unsigned DEFAULT NULL,
  `max_width` smallint(5) unsigned DEFAULT NULL,
  `max_height` smallint(5) unsigned DEFAULT NULL,
  `type` enum('ad','theme') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


LOCK TABLES `grlx_theme_slot` WRITE;
/*!40000 ALTER TABLE `grlx_theme_slot` DISABLE KEYS */;

INSERT INTO `grlx_theme_slot` (`id`, `title`, `label`, `theme_id`, `max_width`, `max_height`, `type`)
VALUES
	(1, 'Slot 1', 'slot-1', 1, 101, 101, 'ad'),
	(2, 'Slot 2', 'slot-2', 1, 101, 101, 'ad'),
	(3, 'Slot 3', 'slot-3', 1, 101, 101, 'ad'),
	(4, 'Slot 4', 'slot-4', 1, 101, 101, 'ad'),
	(5, 'Slot 5', 'slot-5', 1, 101, 101, 'ad');

/*!40000 ALTER TABLE `grlx_theme_slot` ENABLE KEYS */;
UNLOCK TABLES;



# Dump of table grlx_theme_tone
# ------------------------------------------------------------

DROP TABLE IF EXISTS `grlx_theme_tone`;

CREATE TABLE `grlx_theme_tone` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `theme_id` int(11) unsigned DEFAULT NULL,
  `user_made` tinyint(1) unsigned DEFAULT NULL,
  `options` text COLLATE utf8_unicode_ci,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `grlx_theme_tone` WRITE;
/*!40000 ALTER TABLE `grlx_theme_tone` DISABLE KEYS */;

INSERT INTO `grlx_theme_tone` (`id`, `title`, `theme_id`, `user_made`, `options`, `date_created`, `date_modified`)
VALUES
	(1, 'grawlix', 1, NULL, 'tone.grawlix.css', NOW(), NULL),
	(2, 'whiteout', 1, NULL, 'tone.whiteout.css', NOW(), NULL),
	(3, 'desert', 2, NULL, 'tone.desert.css', NOW(), NULL),
	(4, 'moody', 2, NULL, 'tone.moody.css', NOW(), NULL),
	(5, 'natural', 2, NULL, 'tone.natural.css', NOW(), NULL);

/*!40000 ALTER TABLE `grlx_theme_tone` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table grlx_third_function
# ------------------------------------------------------------

DROP TABLE IF EXISTS `grlx_third_function`;

CREATE TABLE `grlx_third_function` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `grlx_third_function` WRITE;
/*!40000 ALTER TABLE `grlx_third_function` DISABLE KEYS */;

INSERT INTO `grlx_third_function` (`id`, `title`, `description`)
VALUES
	(1,'Follow','Follow links allow readers to subscribe to your updates on these services.'),
	(2,'Share','Share links appear next to your comic so readers can post about it to these services.'),
	(3,'Comments','Comment services allow readers to interact with each other and you. Each comic will have its own discussion thread which you can moderate through that service’s website.'),
	(4,'Ads',''),
	(5,'Stats','');

/*!40000 ALTER TABLE `grlx_third_function` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table grlx_third_match
# ------------------------------------------------------------

DROP TABLE IF EXISTS `grlx_third_match`;

CREATE TABLE `grlx_third_match` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `service_id` int(11) unsigned DEFAULT NULL,
  `function_id` int(11) unsigned DEFAULT NULL,
  `active` tinyint(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `grlx_third_match` WRITE;
/*!40000 ALTER TABLE `grlx_third_match` DISABLE KEYS */;

INSERT INTO `grlx_third_match` (`id`, `service_id`, `function_id`, `active`)
VALUES
	(1,1,1,0),
	(2,2,1,0),
	(3,3,1,0),
	(4,4,1,0),
	(6,6,1,0),
	(7,7,1,0),
	(8,8,1,0),
	(9,9,1,0),
	(10,1,2,0),
	(11,2,2,0),
	(12,3,2,0),
	(13,4,2,0),
	(15,7,2,0),
	(16,10,2,0),
	(17,11,2,0),
	(18,12,3,0),
	(19,13,3,0),
	(20,14,3,0),
	(21,15,4,0),
	(22,16,5,0);

/*!40000 ALTER TABLE `grlx_third_match` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table grlx_third_service
# ------------------------------------------------------------

DROP TABLE IF EXISTS `grlx_third_service`;

CREATE TABLE `grlx_third_service` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `label` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `user_info` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `info_title` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `grlx_third_service` WRITE;
/*!40000 ALTER TABLE `grlx_third_service` DISABLE KEYS */;

INSERT INTO `grlx_third_service` (`id`, `title`, `label`, `url`, `description`, `user_info`, `info_title`)
VALUES
	(1,'Twitter','twitter','https://twitter.com','Your username is the same name with which you log in to Twitter. Don’t include the “@”.<br>\nhttps://twitter.com/<span class=\"highlight\">grawlixcomix</span>',NULL,'Username'),
	(2, 'Facebook', 'facebook', 'https://www.facebook.com/', 'Your Facebook Page name follows Facebook’s domain name.<br>\nhttps://www.facebook.com/<span class=\"highlight\">yourpagename</span>', NULL, 'Page name'),
	(3,'Tumblr','tumblr','https://www.tumblr.com','Your username is the same name with which you log in to Tumblr.',NULL,'Username'),
	(4,'Google+','googleplus','https://plus.google.com','Your username is the same name with which you log in to Google+.',NULL,'Username'),
	(6,'Instagram','instagram','http://instagram.com','Your username is the same name with which you log in to Instagram.',NULL,'Username'),
	(7,'Pinterest','pinterest','http://www.pinterest.com','Your username is the same name with which you log in to Pinterest.',NULL,'Username'),
	(8,'LinkedIn','linkedin','https://www.linkedin.com','Your username is the same name with which you log in to LinkedIn.',NULL,'Username'),
	(9,'deviantART','deviantart','http://www.deviantart.com','DeviantArt doesn’t have a public “follow” function, exactly. Readers can tap to see your work at (your username).deviantart.com.',NULL,'Username'),
	(10,'Reddit','reddit','http://www.reddit.com','Send readers to your Reddit page where, if they’re registered, they can subscribe.',NULL,NULL),
	(11,'StumbleUpon','stumbleupon','http://www.stumbleupon.com','Your username is the same name with which you log in to StumbleUpon.',NULL,NULL),
	(12,'Disqus','disqus','https://disqus.com','&lt;div id=\"disqus_thread\"&gt;&lt;/div&gt;<br>\n&lt;script type=\"text/javascript\"&gt;<br>\n&nbsp;&nbsp;&nbsp;/* * * CONFIGURATION VARIABLES: EDIT BEFORE PASTING INTO YOUR WEBPAGE * * */<br>\n&nbsp;&nbsp;&nbsp;var disqus_shortname = \'<span class=\"highlight\">yourname</span>\'; // required: replace example with your forum shortname',NULL,'Short name'),
	(13,'IntenseDebate','intensedebate','http://intensedebate.com','&lt;script&gt;<br>\nvar idcomments_acct = \'<span class=\"highlight\">1234567890abcdefghijklmnopqrstuv</span>\';<br>\nvar idcomments_post_id;<br>\nvar idcomments_post_url;<br>\n&lt;/script&gt;<br>',NULL,'Site account'),
	(15,'Project Wonderful','projectwonderful','https://www.projectwonderful.com','Your Project Wonderful member ID is a number associated with your account. <a href=\"https://www.projectwonderful.com/login.php\">See Project Wonderful</a> for more.',NULL,'Member ID'),
	(16,'Google Analytics','googleanalytics','http://www.google.com/analytics/','Your site’s unique Tracking ID is a string beginning with “UA-”. You can find it in Google Analytics’s “property settings” for your site after signing up for their analytics service at www.google.com/analytics.',NULL,'Tracking ID'),
	(17,'Patreon','patreon','http://www.patreon.com','Your username is the same name with which you log in to Patreon.',NULL,NULL);

/*!40000 ALTER TABLE `grlx_third_service` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table grlx_third_widget
# ------------------------------------------------------------

DROP TABLE IF EXISTS `grlx_third_widget`;

CREATE TABLE `grlx_third_widget` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `service_id` int(11) unsigned DEFAULT NULL,
  `title` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `label` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `value` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `value_title` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `active` tinyint(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `grlx_third_widget` WRITE;
/*!40000 ALTER TABLE `grlx_third_widget` DISABLE KEYS */;

INSERT INTO `grlx_third_widget` (`id`, `service_id`, `title`, `label`, `value`, `value_title`, `description`, `active`)
VALUES
	(1,1,'User timeline','twitter_timeline',NULL,'Widget ID','To add a timeline widget to your site you must first <a class=\"extlink\" target=\"_blank\" rel=\"external\" href=\"https://twitter.com/settings/widgets/new/user\">create it on this Twitter page<i></i></a>. After saving changes, copy the data-widget-id from the code box and paste it below.',0),
	(2,1,'Favorites widget','twitter_favorites',NULL,'Widget ID','To add a favorites widget to your site you must first <a target=\"_blank\" href=\"https://twitter.com/settings/widgets/new/favorites\">create it on this Twitter page</a>. After saving changes, copy the data-widget-id from the code box and paste it below.',0),
	(3,1,'List widget','twitter_list',NULL,'Widget ID','To add a list widget to your site you must first <a target=\"_blank\" href=\"https://twitter.com/settings/widgets/new/list\">create it on this Twitter page</a>. After saving changes, copy the data-widget-id from the code box and paste it below.',0),
	(4,1,'Search widget','twitter_hashtag',NULL,'Widget ID','To add a search widget to your site you must first <a target=\"_blank\" href=\"https://twitter.com/settings/widgets/new/search\">create it on this Twitter page</a>. After saving changes, copy the data-widget-id from the code box and paste it below.',0),
	(5,1,'Collection widget','twitter_collection',NULL,'Widget ID','To add a collection widget to your site you must first <a target=\"_blank\" href=\"https://twitter.com/settings/widgets/new/custom\">create it on this Twitter page</a>. After saving changes, copy the data-widget-id from the code box and paste it below.',0);

/*!40000 ALTER TABLE `grlx_third_widget` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table grlx_user
# ------------------------------------------------------------

DROP TABLE IF EXISTS `grlx_user`;

CREATE TABLE `grlx_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `serial` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `level` tinyint(2) unsigned DEFAULT NULL,
  `email` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `grlx_user` WRITE;
/*!40000 ALTER TABLE `grlx_user` DISABLE KEYS */;

INSERT INTO `grlx_user` (`id`, `username`, `password`, `serial`, `level`, `email`, `date_created`, `date_modified`)
VALUES
	(1,'$user[username]','$user[password]',NULL,10,'$user[email]',NOW(),NULL);

/*!40000 ALTER TABLE `grlx_user` ENABLE KEYS */;
UNLOCK TABLES;

EOL;
		if ( $str ) {
			$this->dbString = $str;
		}
	}


	/**
	 * Make some directories in assets
	 *
	 * @return boolean - success or fail
	 */
	protected function makeAssetDirs() {
		$path = './assets/';
		$dirs[] = 'data';
		$dirs[] = 'images/ads';
		$dirs[] = 'images/comics';
		$dirs[] = 'images/icons';
		$dirs[] = 'images/static';
		$dirs[] = 'images/theme';
		$x = 0;
		if ( is_dir($path) ) {
			foreach ( $dirs as $dir ) {
				if ( !is_dir($path.$dir))
				{
					if ( @mkdir($path.$dir,0755,true) ) {
						$x++;
					}
					else
					{
						$dir_problem_list[] = $path.$dir;
					}
				}
			}
		}
		return @$dir_problem_list;
	}
}


/* ! Setup * * * * * * * */

mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
date_default_timezone_set('America/Los_Angeles');
require_once('_system/password.php');


/* ! Run * * * * * * * */

// First page load
if ( !$_POST ) {
	$config = 'config.php';
	if ( file_exists($config) ) {
		include_once($config);
		if ( @$setup )
		{
			$args['action'] = 'precheck';
			$args['dbInfo'] = $setup;
		}
	}
}
if ( !@$args )
{
	$args['action'] = '1';
}
// Check the user's db info
if ( $_POST && is_array($_POST['dbCred']) ) {
	$args['action'] = '2';
	$args['dbInfo'] = $_POST['dbCred'];
	$args['timezone'] = @$_POST['timezone'];
}

// Check the user info & complete
if ( $_POST && is_array(@$_POST['user']) ) {
	$args['action'] = '3';
	$args['user'] = $_POST['user'];
	$args['dbInfo'] = $_POST['dbCred'];
	$args['timezone'] = $_POST['timezone'];
}

$firstRun = new GrlxFirstRun($args);


/* ! Display * * * * * * * */

header("Content-Type: text/html; charset=utf-8");?>
<!doctype html>
<html class="no-js" lang="en">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Install the Grawlix CMS — <?=$firstRun->step?></title>
	<!--link href='http://fonts.googleapis.com/css?family=Fira+Sans:300,400,500,700|Fira+Mono:400,700' rel='stylesheet' type='text/css' /-->
	<link rel="stylesheet" href="assets/system/firstrun/firstrun.css" />
	<script src="assets/scripts/modernizr.min.js"></script>
</head>
<body>
	<div class="wrapper">
		<form accept-charset="UTF-8" action="<?=$firstRun->sendTo?>" method="post" data-abide>
			<main>
				<header>
					<img src="assets/system/images/logo_small.svg">
					<h1>Install: <?=$firstRun->step?></h1>
				</header>
<?=@$firstRun->message?>
<?=$firstRun->content?>
			</main>
		</form>
	</div>
	<script src="assets/system/firstrun/firstrun.min.js"></script>
</body>
</html>
