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
		"website"		=> "",
		"author"		=> "Aries-Belgium",
		"authorsite"	=> "http://community.mybb.com/user-3840.html",
		"version"		=> "1.0",
		"guid" 			=> "40004d07553e6778cc325161c70a3979",
		"compatibility" => "14*,16*"
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
	global $mybb, $subject, $message, $send;
	
	if(isset($mybb->input['pid']))
	{
		$_post = get_post($mybb->input['pid']);
		$subject = (strpos($_post['subject'], "RE:") === false ? "RE: " : "") .htmlspecialchars_uni($_post['subject']);
		$message = "[quote='".htmlspecialchars_uni($_post['username'])."' pid='".intval($_post['pid'])."' dateline='".intval($_post['dateline'])."']\n".htmlspecialchars_uni($_post['message'])."\n[/quote]";
		
		// mybb 1.4 compatibility
		$send = str_replace(
			array("name=\"subject\"", "<textarea name=\"message\" id=\"message\" rows=\"20\" cols=\"70\" tabindex=\"4\">"),
			array("name=\"subject\" value=\"{$subject}\"", "<textarea name=\"message\" id=\"message\" rows=\"20\" cols=\"70\" tabindex=\"4\">{$message}"),
			$send
		);
	}
}