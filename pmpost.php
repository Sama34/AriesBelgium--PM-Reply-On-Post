<?php
/**
 * PM Reply To Post
 * Copyright 2011 Aries-Belgium
 *
 * $Id$
 */

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$plugins->add_hook('private_send_end', 'pmpost_quote_post');

/**
 * Info function for MyBB plugin system
 */
function pmpost_info()
{
	$donate_button = 
'<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=RQNL345SN45DS" style="float:right;margin-top:-8px;padding:4px;" target="_blank"><img src="https://www.paypalobjects.com/WEBSCR-640-20110306-1/en_US/i/btn/btn_donate_SM.gif" /></a>';

	return array(
		"name"			=> "PM Reply On Post",
		"description"	=> $donate_button."Allows you to reply on a post via PM.",
		"website"		=> "http://mods.mybb.com/view/pm-reply-to-post",
		"author"		=> "Aries-Belgium",
		"authorsite"	=> "http://community.mybb.com/user-3840.html",
		"version"		=> "1.1",
		"guid" 			=> "40004d07553e6778cc325161c70a3979",
		"compatibility" => "16*"
	);
}

/**
 * The activation function for the plugin system
 */
function pmpost_activate()
{
	include MYBB_ROOT."/inc/adminfunctions_templates.php";
	find_replace_templatesets("postbit_pm", "#".preg_quote("uid={\$post['uid']}")."#s", "uid={\$post['uid']}&amp;pid={\$post['pid']}");
}

/**
 * The activation function for the plugin system
 */
function pmpost_deactivate()
{
	include MYBB_ROOT."/inc/adminfunctions_templates.php";
	find_replace_templatesets("postbit_pm", "#".preg_quote("&amp;pid={\$post['pid']}")."#s", "",0);
}

/**
 * Implementation of the private_send_end hook
 *
 * Quotes the post from the url in the pm
 */
function pmpost_quote_post()
{
	global $mybb;
	
	if(isset($mybb->input['pid']))
	{
		global $db, $subject, $message, $send;

		// Fetch unviewable forums
		$unviewable_forums = get_unviewable_forums();
		if($unviewable_forums)
		{
			$unviewable_forums = 'AND p.fid NOT IN ('.$unviewable_forums.')';
		}

		$query = $db->simple_select('posts p LEFT JOIN '.TABLE_PREFIX.'users u ON (u.uid=p.uid)', 'p.subject, p.message, p.pid, p.tid, p.username, p.dateline, p.fid, p.visible, u.username AS userusername', 'p.pid=\''(int)$mybb->input['pid']'\''.$unviewable_forums, array('limit' => 1));

		while($quoted_post = $db->fetch_array($query))
		{
			if(!is_moderator($quoted_post['fid']) && $quoted_post['visible'] == 0)
			{
				continue;
			}

			require_once MYBB_ROOT."inc/functions_posting.php";

			$subject = (!my_strpos($quoted_post['subject'], "RE:") ? "RE: " : "").htmlspecialchars_uni($quoted_post['subject']); // should we parse bad words here?
			$message = parse_quoted_message($quoted_post);
		}
	}
}