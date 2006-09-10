<?php
/**
 * MyBB 1.2
 * Copyright � 2006 MyBB Group, All Rights Reserved
 *
 * Website: http://www.mybboard.com
 * License: http://www.mybboard.com/eula.html
 *
 * $Id$
 */

/**
 * Upgrade Script: 1.0.x / 1.1.x
 */


$upgrade_detail = array(
	"revert_all_templates" => 1,
	"revert_all_themes" => 1,
	"revert_all_settings" => 2
	);

@set_time_limit(0);

function upgrade5_dbchanges()
{
	global $db, $output, $mybb;

	$output->print_header("Performing Queries");

	echo "<p>Performing necessary upgrade queries..</p>";
	
	$db->query("ALTER TABLE ".TABLE_PREFIX."users CHANGE avatartype avatartype varchar(10) NOT NULL;");
	if(!$db->field_exists('totalpms', TABLE_PREFIX."users"))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."users ADD totalpms int(10) NOT NULL default '0' AFTER showcodebuttons;");
	}
	
	if(!$db->field_exists('newpms', TABLE_PREFIX."users"))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."users ADD newpms int(10) NOT NULL default '0' AFTER totalpms;");
	}
	
	if(!$db->field_exists('unreadpms', TABLE_PREFIX."users"))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."users ADD unreadpms int(10) NOT NULL default '0' AFTER newpms;");
	}
	
	if(!$db->field_exists('showredirect', TABLE_PREFIX."users"))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."users ADD showredirect char(3) NOT NULL default '' AFTER showquickreply;");
	}
	
	if(!$db->field_exists('avatardimensions', TABLE_PREFIX."users"))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."users ADD avatardimensions varchar(10) NOT NULL default '' AFTER avatar;");
	}
	
	if(!$db->field_exists('unapprovedposts', TABLE_PREFIX."threads"))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."threads ADD unapprovedposts INT(10) unsigned NOT NULL default '0' AFTER visible;");
	}
	
	if(!$db->field_exists('unapprovedthreads', TABLE_PREFIX."forums"))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."forums ADD unapprovedthreads INT(10) unsigned NOT NULL default '0' AFTER rules;");
	}
	
	if(!$db->field_exists('unapprovedposts', TABLE_PREFIX."forums"))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."forums ADD unapprovedposts INT(10) unsigned NOT NULL default '0' AFTER rules;");
	}
	
	if(!$db->field_exists('defaultdatecut', TABLE_PREFIX."forums"))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."forums ADD defaultdatecut smallint(4) unsigned NOT NULL default '0' AFTER unapprovedposts;");
	}
	
	if(!$db->field_exists('defaultsortby', TABLE_PREFIX."forums"))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."forums ADD defaultsortby varchar(10) NOT NULL default '' AFTER defaultdatecut;");
	}
	
	if(!$db->field_exists('defaultsortorder', TABLE_PREFIX."forums"))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."forums ADD defaultsortorder varchar(4) NOT NULL default '' AFTER defaultsortby;");
	}
	
	if(!$db->field_exists('lastposteruid', TABLE_PREFIX."forums"))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."forums ADD lastposteruid int(10) unsigned NOT NULL default '0' AFTER lastposter;");
	}
	
	if(!$db->field_exists('lastpostsubject', TABLE_PREFIX."forums"))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."forums ADD lastpostsubject varchar(120) NOT NULL default '' AFTER lastposttid");
	}
	
	if(!$db->field_exists('lastposteruid', TABLE_PREFIX."threads"))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."threads ADD lastposteruid int unsigned NOT NULL default '0' AFTER lastposter");
	}
	
	if(!$db->field_exists('canmanagemembers', TABLE_PREFIX."groupleaders"))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."groupleaders ADD canmanagemembers char(3) NOT NULL default '' AFTER uid;");
	}
	
	if(!$db->field_exists('canmanagerequests', TABLE_PREFIX."groupleaders"))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."groupleaders ADD canmanagerequests char(3) NOT NULL default '' AFTER canmanagemembers;");
	}
	
	if(!$db->field_exists('caneditlangs', TABLE_PREFIX."adminoptions"))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."adminoptions ADD caneditlangs char(3) NOT NULL default '' AFTER canedithelp;");
	}
	
	if(!$db->field_exists('canrundbtools', TABLE_PREFIX."adminoptions"))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."adminoptions ADD canrundbtools char(3) NOT NULL default ''");
	}
	
	if(!$db->field_exists('allowedgroups', TABLE_PREFIX."themes"))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."themes ADD allowedgroups text NOT NULL default '' AFTER extracss;");
	}
	
	if(!$db->field_exists('canmovetononmodforum', TABLE_PREFIX."moderators"))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."moderators ADD canmovetononmodforum char(3) NOT NULL default '' AFTER canmanagethreads;");
	}
	
	if(!$db->field_exists('csscached', TABLE_PREFIX."themes"))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."themes ADD csscached bigint(30) NOT NULL default '0'");
	}
	
	$db->query("UPDATE ".TABLE_PREFIX."adminoptions SET caneditlangs='yes' WHERE canrunmaint='yes'");
	$db->query("UPDATE ".TABLE_PREFIX."adminoptions SET caneditlangs='no' WHERE canrunmaint='no'");
	$db->query("UPDATE ".TABLE_PREFIX."adminoptions SET canrundbtools='yes' WHERE canrunmaint='yes'");
	$db->query("UPDATE ".TABLE_PREFIX."adminoptions SET canrundbtools='no' WHERE canrunmaint='no'");
	$db->query("UPDATE ".TABLE_PREFIX."settings SET optionscode='select\r\ninstant=Instant Activation\r\nverify=Send Email Verification\r\nrandompass=Send Random Password\r\nadmin=Administrator Activation' WHERE name = 'regtype'");
	$db->query("UPDATE ".TABLE_PREFIX."users SET totalpms='-1', newpms='-1', unreadpms='-1'");
	$db->query("UPDATE ".TABLE_PREFIX."settings SET name='maxmessagelength' WHERE name='messagelength'");

	$db->query("DROP TABLE IF EXISTS ".TABLE_PREFIX."mycode");
	$db->query("CREATE TABLE ".TABLE_PREFIX."mycode (
		    cid int unsigned NOT NULL auto_increment,
		    title varchar(100) NOT NULL default '',
		    description text NOT NULL default '',
		    regex text NOT NULL default '',
		    replacement text NOT NULL default '',
		    active char(3) NOT NULL default '',
			PRIMARY KEY(cid)
		) TYPE=MyISAM;");

	$db->query("DROP TABLE IF EXISTS ".TABLE_PREFIX."templategroups");
	$db->query("CREATE TABLE ".TABLE_PREFIX."templategroups (
			gid int unsigned NOT NULL auto_increment,
			prefix varchar(50) NOT NULL default '',
			title varchar(100) NOT NULL default '',
			PRIMARY KEY (gid)
			) TYPE=MyISAM;");

	$db->query("INSERT INTO ".TABLE_PREFIX."templategroups (gid,prefix,title) VALUES ('1','calendar','<lang:group_calendar>');");
	$db->query("INSERT INTO ".TABLE_PREFIX."templategroups (gid,prefix,title) VALUES ('2','editpost','<lang:group_editpost>');");
	$db->query("INSERT INTO ".TABLE_PREFIX."templategroups (gid,prefix,title) VALUES ('3','email','<lang:group_email>');");
	$db->query("INSERT INTO ".TABLE_PREFIX."templategroups (gid,prefix,title) VALUES ('4','emailsubject','<lang:group_emailsubject>');");
	$db->query("INSERT INTO ".TABLE_PREFIX."templategroups (gid,prefix,title) VALUES ('5','forumbit','<lang:group_forumbit>');");
	$db->query("INSERT INTO ".TABLE_PREFIX."templategroups (gid,prefix,title) VALUES ('6','forumjump','<lang:group_forumjump>');");
	$db->query("INSERT INTO ".TABLE_PREFIX."templategroups (gid,prefix,title) VALUES ('7','forumdisplay','<lang:group_forumdisplay>');");
	$db->query("INSERT INTO ".TABLE_PREFIX."templategroups (gid,prefix,title) VALUES ('8','index','<lang:group_index>');");
	$db->query("INSERT INTO ".TABLE_PREFIX."templategroups (gid,prefix,title) VALUES ('9','error','<lang:group_error>');");
	$db->query("INSERT INTO ".TABLE_PREFIX."templategroups (gid,prefix,title) VALUES ('10','memberlist','<lang:group_memberlist>');");
	$db->query("INSERT INTO ".TABLE_PREFIX."templategroups (gid,prefix,title) VALUES ('11','multipage','<lang:group_multipage>');");
	$db->query("INSERT INTO ".TABLE_PREFIX."templategroups (gid,prefix,title) VALUES ('12','private','<lang:group_private>');");
	$db->query("INSERT INTO ".TABLE_PREFIX."templategroups (gid,prefix,title) VALUES ('13','portal','<lang:group_portal>');");
	$db->query("INSERT INTO ".TABLE_PREFIX."templategroups (gid,prefix,title) VALUES ('14','postbit','<lang:group_postbit>');");
	$db->query("INSERT INTO ".TABLE_PREFIX."templategroups (gid,prefix,title) VALUES ('15','redirect','<lang:group_redirect>');");
	$db->query("INSERT INTO ".TABLE_PREFIX."templategroups (gid,prefix,title) VALUES ('16','showthread','<lang:group_showthread>');");
	$db->query("INSERT INTO ".TABLE_PREFIX."templategroups (gid,prefix,title) VALUES ('17','usercp','<lang:group_usercp>');");
	$db->query("INSERT INTO ".TABLE_PREFIX."templategroups (gid,prefix,title) VALUES ('18','online','<lang:group_online>');");
	$db->query("INSERT INTO ".TABLE_PREFIX."templategroups (gid,prefix,title) VALUES ('19','moderation','<lang:group_moderation>');");
	$db->query("INSERT INTO ".TABLE_PREFIX."templategroups (gid,prefix,title) VALUES ('20','nav','<lang:group_nav>');");
	$db->query("INSERT INTO ".TABLE_PREFIX."templategroups (gid,prefix,title) VALUES ('21','search','<lang:group_search>');");
	$db->query("INSERT INTO ".TABLE_PREFIX."templategroups (gid,prefix,title) VALUES ('22','showteam','<lang:group_showteam>');");
	$db->query("INSERT INTO ".TABLE_PREFIX."templategroups (gid,prefix,title) VALUES ('23','reputation','<lang:group_reputation>');");
	$db->query("INSERT INTO ".TABLE_PREFIX."templategroups (gid,prefix,title) VALUES ('24','newthread','<lang:group_newthread>');");
	$db->query("INSERT INTO ".TABLE_PREFIX."templategroups (gid,prefix,title) VALUES ('25','newreply','<lang:group_newreply>');");
	$db->query("INSERT INTO ".TABLE_PREFIX."templategroups (gid,prefix,title) VALUES ('26','member','<lang:group_member>');");

	$db->query("DROP TABLE IF EXISTS ".TABLE_PREFIX."searchlog");
	$db->query("CREATE TABLE ".TABLE_PREFIX."searchlog (
		  sid varchar(32) NOT NULL default '',
		  uid int unsigned NOT NULL default '0',
		  dateline bigint(30) NOT NULL default '0',
		  ipaddress varchar(120) NOT NULL default '',
		  threads text NOT NULL default '',
		  posts text NOT NULL default '',
		  searchtype varchar(10) NOT NULL default '',
		  resulttype varchar(10) NOT NULL default '',
		  querycache text NOT NULL default '',
		  PRIMARY KEY  (sid)
		) TYPE=MyISAM;");

	$db->query("UPDATE ".TABLE_PREFIX."settings SET name='bannedemails' WHERE name='emailban' LIMIT 1");
	$db->query("UPDATE ".TABLE_PREFIX."settings SET name='bannedips' WHERE name='ipban' LIMIT 1");

	$query = $db->query("SELECT value FROM ".TABLE_PREFIX."settings WHERE name='bannedusernames'");
	$bannedusernames = $db->fetch_field($query, 'sid');
	$bannedusernames = explode(" ", $bannedusernames);
	$bannedusernames = implode(",", $bannedusernames);
	$query = $db->query("UPDATE ".TABLE_PREFIX."settings SET value='".$db->escape_string($bannedusernames)."' WHERE name='bannedusernames'");

	$query = $db->query("SELECT value FROM ".TABLE_PREFIX."settings WHERE name='bannedemails'");
	$bannedemails = $db->fetch_field($query, 'sid');
	$bannedemails = explode(" ", $bannedemails);
	$bannedemails = implode(",", $bannedemails);
	$query = $db->query("UPDATE ".TABLE_PREFIX."settings SET value='".$db->escape_string($bannedemails)."' WHERE name='bannedemails'");

	$query = $db->query("SELECT value FROM ".TABLE_PREFIX."settings WHERE name='bannedips'");
	$bannedips = $db->fetch_field($query, 'sid');
	$bannedips = explode(" ", $bannedips);
	$bannedips = implode(",", $bannedips);
	$query = $db->query("UPDATE ".TABLE_PREFIX."settings SET value='".$db->escape_string($bannedips)."' WHERE name='bannedips'");

	$query = $db->query("DROP TABLE IF EXISTS ".TABLE_PREFIX."reputation");

	$query = $db->query("CREATE TABLE ".TABLE_PREFIX."reputation (
	  rid int unsigned NOT NULL auto_increment,
	  uid int unsigned NOT NULL default '0',
	  adduid int unsigned NOT NULL default '0',
	  reputation bigint(30) NOT NULL default '0',
	  dateline bigint(30) NOT NULL default '0',
	  comments text NOT NULL,
      PRIMARY KEY(rid)
	) TYPE=MyISAM;");

	$db->query("DROP TABLE IF EXISTS ".TABLE_PREFIX."mailqueue");
	$query = $db->query("CREATE TABLE ".TABLE_PREFIX."mailqueue (
		mid int unsigned NOT NULL auto_increment,
		mailto varchar(200) NOT NULL,
		mailfrom varchar(200) NOT NULL,
		subject varchar(200) NOT NULL,
		message text NOT NULL,
		headers text NOT NULL,
		PRIMARY KEY(mid)
	) TYPE=MyISAM;");

	$db->query("UPDATE ".TABLE_PREFIX."users SET reputation='0'");

	$db->query("UPDATE ".TABLE_PREFIX."usergroups SET reputationpower='1'");
	$db->query("UPDATE ".TABLE_PREFIX."usergroups SET reputationpower='2' WHERE cancp='yes'");

	if($db->field_exists('rating', TABLE_PREFIX."users"))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."users DROP rating;");
	}
	
	if(!$db->field_exists('attachmentcount', TABLE_PREFIX."threads"))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."threads ADD attachmentcount int(10) unsigned NOT NULL default '0'");
	}
	
	if(!$db->field_exists('posthash', TABLE_PREFIX."posts"))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."posts ADD posthash varchar(32) NOT NULL default '' AFTER visible");
	}
	
	$db->query("ALTER TABLE ".TABLE_PREFIX."attachtypes CHANGE extension extension varchar(10) NOT NULL;");
	
	if(!$db->field_exists('deletetime', TABLE_PREFIX."threads"))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."threads ADD deletetime int(10) unsigned NOT NULL default '0' AFTER attachmentcount");
	}
	
	if(!$db->field_exists('loginattempts', TABLE_PREFIX."sessions"))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."sessions ADD loginattempts tinyint(2) NOT NULL default '1'");
	}
	
	if(!$db->field_exists('failedlogin', TABLE_PREFIX."sessions"))
	{
  		$db->query("ALTER TABLE ".TABLE_PREFIX."sessions ADD failedlogin bigint(30) NOT NULL default '0'");
	}
	
	if(!$db->field_exists('canviewthreads', TABLE_PREFIX."usergroups"))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."usergroups ADD canviewthreads char(3) NOT NULL default '' AFTER canview");
	}
	
	if(!$db->field_exists('canviewthreads', TABLE_PREFIX."forumpermissions"))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."forumpermissions ADD canviewthreads char(3) NOT NULL default '' AFTER canview");
	}
	
	$db->query("DROP TABLE IF EXISTS ".TABLE_PREFIX."captcha");
	$db->query("CREATE TABLE ".TABLE_PREFIX."captcha (
	  imagehash varchar(32) NOT NULL default '',
	  imagestring varchar(8) NOT NULL default '',
	  dateline bigint(30) NOT NULL default '0'
	) TYPE=MyISAM;");

	if(!$db->field_exists('data', TABLE_PREFIX."moderatorlog"))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."moderatorlog ADD data text NOT NULL default '' AFTER action;");
	}
	
	$db->query("DROP TABLE IF EXISTS ".TABLE_PREFIX."adminsessions");
	$db->query("CREATE TABLE ".TABLE_PREFIX."adminsessions (
		sid varchar(32) NOT NULL default '',
		uid int unsigned NOT NULL default '0',
		loginkey varchar(50) NOT NULL default '',
		ip varchar(40) NOT NULL default '',
		dateline bigint(30) NOT NULL default '0',
		lastactive bigint(30) NOT NULL default '0'
	) TYPE=MyISAM;");

	$db->query("DROP TABLE IF EXISTS ".TABLE_PREFIX."modtools");
	$db->query("CREATE TABLE ".TABLE_PREFIX."modtools (
	tid smallint unsigned NOT NULL auto_increment,
	name varchar(200) NOT NULL,
	description text NOT NULL,
	forums text NOT NULL,
	type char(1) NOT NULL default '',
	postoptions text NOT NULL,
	threadoptions text NOT NULL,
	PRIMARY KEY (tid)
) TYPE=MyISAM;");

	if(!$db->field_exists('disporder', TABLE_PREFIX."usergroups"))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."usergroups ADD disporder smallint(6) NOT NULL default '0' AFTER image");
	}
	
	$db->query("UPDATE ".TABLE_PREFIX."usergroups SET canviewthreads=canview");
	$db->query("UPDATE ".TABLE_PREFIX."forumpermissions SET canviewthreads=canview");

	$contents .= "Done</p>";
	$contents .= "<p>Click next to continue with the upgrade process.</p>";
	$output->print_contents($contents);
	$output->print_footer("5_redoconfig");
}

function upgrade5_redoconfig()
{
	global $db, $output, $config, $mybb;
	$output->print_header("Rewriting config.php");

	$uid = 0;
	if($mybb->input['username'] != '' && !$mybb->input['uid'])
	{
		$query = $db->simple_select(TABLE_PREFIX."users", "uid", "username='".$db->escape_string($mybb->input['username'])."'");
		$uid = $db->fetch_field($query, "uid");
		if(!$uid)
		{
			echo "<p><span style=\"color: red; font-weight: bold;\">The username you entered could not be found.</span><br />Please ensure you corectly enter a valid username.</p>";
		}
	}
	else if($mybb->input['uid'])
	{
		$uid = $mybb->input['uid'];
	}

	if(!$uid)
	{
		echo "<p>Please enter your primary administrator username. The user ID of the username you enter here will be written in to the new configuration file which will prevent this account from being banned, edited or deleted.</p>";
		echo "<p>Username:</p>";
		echo "<p><input type=\"text\" name=\"username\" value=\"\" />";
		$output->print_footer("5_redoconfig");
		exit;
	}

	$fh = @fopen(MYBB_ROOT."/inc/config.php", "w");
	if(!$fh)
	{
		echo "<p><span style=\"color: red; font-weight: bold;\">Unable to open inc/config.php</span><br />Before the upgrade process can continue, you need to changes the permissions of inc/config.php so it is writable.</p><input type=\"hidden\" name=\"uid\" value=\"{$uid}\" />";
		$output->print_footer("5_redoconfig");
		exit;
	}
	
	if(!$config['admindir'])
	{
		$config['admindir'] = "admin";
	}
	
	if(!$config['cachestore'])
	{
		$config['cachestore'] = "db";
	}
	$configdata = "<?php
/**
 * Daatabase configuration
 */

\$config['dbtype'] = '{$config['dbtype']}';
\$config['hostname'] = '{$config['hostname']}';
\$config['username'] = '{$config['username']}';
\$config['password'] = '{$config['password']}';
\$config['database'] = '{$config['database']}';
\$config['table_prefix'] = '{$config['table_prefix']}';

/**
 * Admin CP directory
 *  For security reasons, it is recommended you
 *  rename your Admin CP directory. You then need
 *  to adjust the value below to point to the
 *  new directory.
 */

\$config['admin_dir'] = '{$config['admindir']}';

/**
 * Hide all Admin CP links
 *  If you wish to hide all Admin CP links
 *  on the front end of the board after
 *  renaming your Admin CP directory, set this
 *  to 1.
 */

\$config['hide_admin_links'] = 0;

/**
 * Data-cache configuration
 *  The data cache is a temporary cache
 *  of the most commonly accessed data in MyBB.
 *  By default, the database is used to store this data.
 *
 *  If you wish to use the file system (inc/cache directory)
 *  you can change the value below to 'files' from 'db'.
 */

\$config['cache_store'] = '{$config['cachestore']}';

/**
 * Super Administrators
 *  A comma separated list of user IDs who cannot
 *  be edited, deleted or banned in the Admin CP.
 *  The administrator permissions for these users
 *  cannot be altered either.
 */

\$config['super_admins'] = '{$uid}';

?".">";

	fwrite($fh, $configdata);
	fclose($fh);
	echo "<p>The configuration file has successfully been rewritten.</p>";
	echo "<p>Click next to continue with the upgrade process.</p>";
	$output->print_footer("5_lastposts");

}

function upgrade5_lastposts()
{
	global $db, $output;
	$output->print_header("Rebuilding Last Post Columns");

	if(!$_POST['tpp'])
	{
		echo "<p>The next step in the upgrade process involves rebuilding the last post information for every thread in your forum. Below, please enter the number of threads to process per page.</p>";
		echo "<p><strong>Threads Per Page:</strong> <input type=\"text\" size=\"3\" value=\"200\" name=\"tpp\" /></p>";
		echo "<p>Once you're ready, press next to begin the rebuild process.</p>";
		$output->print_footer("5_lastposts");
	}
	else
	{
		$query = $db->simple_select(TABLE_PREFIX."threads", "COUNT(*) as num_threads");
		$num_threads = $db->fetch_field($query, 'num_threads');
		$tpp = intval($_POST['tpp']);
		$start = intval($_POST['start']);
		$end = $start+$tpp;
		if($end > $num_threads)
		{
			$end = $num_threads;
		}
		echo "<p>Updating {$start} to {$end} of {$num_threads}...</p>";
		$query = $db->simple_select(TABLE_PREFIX."threads", "tid, firstpost", "", array("order_by" => "tid", "order_dir" => "asc", "limit" => $tpp, "limit_start" => $start));
		while($thread = $db->fetch_array($query))
		{
			update_thread_count($thread['tid']);
			if($thread['firstpost'] == 0)
			{
				update_first_post($thread['tid']);
			}
		}
		echo "<p>Done</p>";
		if($end >= $num_threads)
		{
			echo "<p>The rebuild process has completed successfully. Click next to continue with the upgrade.";
			$output->print_footer("5_forumlastposts");
		}
		else
		{
			echo "<p>Click Next to continue with the build process.</p>";
			echo "<input type=\"hidden\" name=\"tpp\" value=\"{$tpp}\" />";
			echo "<input type=\"hidden\" name=\"start\" value=\"{$end}\" />";
			$output->print_footer("5_lastposts");
		}
	}
}

function upgrade5_forumlastposts()
{
	global $db, $output;
	$output->print_header("Rebuilding Forum Last Posts");
	echo "<p>Rebuilding last post information for forums..</p>";
	$query = $db->simple_select(TABLE_PREFIX."forums", "fid");
	while($forum = $db->fetch_array($query))
	{
		update_forum_count($forum['fid']);
	}
	echo "<p>Done";
	echo "<p>Click next to continue with the upgrade process.</p>";
	$output->print_footer("5_indexes");
}

function upgrade5_indexes()
{
	global $db, $output;

	$output->print_header("Indexing");
	echo "<p>Checking and creating fulltext database indexes..</p>";


	if($db->is_fulltext(TABLE_PREFIX."threads", "subject"))
	{
		$db->drop_index(TABLE_PREFIX."threads", "subject");
	}
	if($db->is_fulltext(TABLE_PREFIX."threads", "subject_2"))
	{
		$db->drop_index(TABLE_PREFIX."threads", "subject_2");
	}

	if($db->supports_fulltext(TABLE_PREFIX."threads"))
	{
		$db->create_fulltext_index(TABLE_PREFIX."threads", "subject");
	}
	if($db->supports_fulltext_boolean(TABLE_PREFIX."posts"))
	{
		if(!$db->is_fulltext(TABLE_PREFIX."posts", "message"))
		{
			$db->create_fulltext_index(TABLE_PREFIX."posts", "message");
		}
	}

	// Register a shutdown function which actually tests if this functionality is working
	add_shutdown('test_shutdown_function');

	$contents .= "Click next to continue with the upgrade process.</p>";
	$output->print_contents($contents);
	$output->print_footer("5_done");
}



function test_shutdown_function()
{
	global $db;
	$db->query("UPDATE ".TABLE_PREFIX."settings SET value='yes' WHERE name='useshutdownfunc'");
	write_settings();
}
?>