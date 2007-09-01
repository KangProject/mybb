<?php
/**
 * MyBB 1.2
 * Copyright � 2007 MyBB Group, All Rights Reserved
 *
 * Website: http://www.mybboard.net
 * License: http://www.mybboard.net/license.php
 *
 * $Id$
 */

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$page->add_breadcrumb_item($lang->spiders_bots, "index.php?".SID."&amp;module=config/spiders");

if($mybb->input['action'] == "add")
{
	if($mybb->request_method == "post")
	{
		if(!trim($mybb->input['name']))
		{
			$errors[] = $lang->error_missing_name;
		}

		if(!trim($mybb->input['useragent']))
		{
			$errors[] = $lang->error_missing_agent;
		}

		if(!$errors)
		{
			$new_spider = array(
				"name" => $db->escape_string($mybb->input['name']),
				"theme" => intval($mybb->input['theme']),
				"language" => $db->escape_string($mybb->input['language']),
				"usergroup" => intval($mybb->input['usergroup']),
				"useragent" => $db->escape_string($mybb->input['useragent']),
				"lastvisit" => 0
			);
			$sid = $db->insert_query("spiders", $new_spider);

			$cache->update_spiders();

			// Log admin action
			log_admin_action($sid, $mybb->input['name']);

			flash_message($lang->success_bot_created, 'success');
			admin_redirect("index.php?".SID."&module=config/spiders");
		}
	}

	$page->add_breadcrumb_item($lang->add_new_bot);
	$page->output_header($lang->spiders_bots." - ".$lang->add_new_bot);
	
	$form = new Form("index.php?".SID."&amp;module=config/spiders&amp;action=add", "post");

	if($errors)
	{
		$page->output_inline_error($errors);
	}

	$form_container = new FormContainer($lang->add_new_bot);
	$form_container->output_row($lang->name." <em>*</em>", $lang->name_desc, $form->generate_text_box('name', $mybb->input['name'], array('id' => 'name')), 'name');
	$form_container->output_row($lang->user_agent." <em>*</em>", $lang->user_agent_desc, $form->generate_text_box('useragent', $mybb->input['useragent'], array('id' => 'useragent')), 'useragent');
	
	$languages = array('' => $lang->use_board_default);
	$languages = array_merge($languages, $lang->get_languages());
	$form_container->output_row($lang->language, $lang->language_desc, $form->generate_select_box("language", $languages, $mybb->input['language'], array("id" => "language")), 'language');
	
	$form_container->output_row($lang->theme, $lang->theme_desc, build_theme_select("theme", $mybb->input['theme'], 0, "", 1), 'theme');

	$query = $db->simple_select("usergroups", "*", "", array("order_by" => "title", "order_dir" => "asc"));
	while($usergroup = $db->fetch_array($query))
	{
		$usergroups[$usergroup['gid']] = $usergroup['title'];
	}
	if(!$mybb->input['usergroup'])
	{
		$mybb->input['usergroup'] = 1;
	}
	$form_container->output_row($lang->user_group, $lang->user_group_desc, $form->generate_select_box("usergroup", $usergroups, $mybb->input['usergroup'], array("id" => "usergroup")), 'usergroup');


	$form_container->end();
	$buttons[] = $form->generate_submit_button($lang->save_bot);
	$form->output_submit_wrapper($buttons);
	$form->end();
	
	$page->output_footer();
}

if($mybb->input['action'] == "delete")
{
	$query = $db->simple_select("spiders", "*", "sid='".intval($mybb->input['sid'])."'");
	$spider = $db->fetch_array($query);

	// Does the spider not exist?
	if(!$spider['sid'])
	{
		flash_message($lang->error_invalid_bot, 'error');
		admin_redirect("index.php?".SID."&module=config/spiders");
	}

	// User clicked no
	if($mybb->input['no'])
	{
		admin_redirect("index.php?".SID."&module=config/spiders");
	}

	if($mybb->request_method == "post")
	{
		// Delete the spider
		$db->delete_query("spiders", "sid='{$spider['sid']}'");

		$cache->update_spiders();

		// Log admin action
		log_admin_action($mybb->input['name']);

		flash_message($lang->success_bot_deleted, 'success');
		admin_redirect("index.php?".SID."&module=config/spiders");
	}
	else
	{
		$page->output_confirm_action("index.php?".SID."&module=config/spiders&action=delete&sid={$spider['sid']}", $lang->confirm_bot_deletion);
	}
}

if($mybb->input['action'] == "edit")
{
	$query = $db->simple_select("spiders", "*", "sid='".intval($mybb->input['sid'])."'");
	$spider = $db->fetch_array($query);

	// Does the spider not exist?
	if(!$spider['sid'])
	{
		flash_message($lang->error_invalid_bot, 'error');
		admin_redirect("index.php?".SID."&module=config/spiders");
	}

	if($mybb->request_method == "post")
	{
		if(!trim($mybb->input['name']))
		{
			$errors[] = $lang->error_missing_name;
		}

		if(!trim($mybb->input['useragent']))
		{
			$errors[] = $lang->error_missing_agent;
		}

		if(!$errors)
		{
			$updated_spider = array(
				"name" => $db->escape_string($mybb->input['name']),
				"theme" => intval($mybb->input['theme']),
				"language" => $db->escape_string($mybb->input['language']),
				"usergroup" => intval($mybb->input['usergroup']),
				"useragent" => $db->escape_string($mybb->input['useragent'])
			);
			$db->update_query("spiders", $updated_spider, "sid='{$spider['sid']}'");

			$cache->update_spiders();

			// Log admin action
			log_admin_action($spider['sid'], $mybb->input['name']);

			flash_message($lang->success_bot_updated, 'success');
			admin_redirect("index.php?".SID."&module=config/spiders");
		}
	}

	$page->add_breadcrumb_item($lang->edit_bot);
	$page->output_header($lang->spiders_bots." - ".$lang->edit_bot);
	
	$form = new Form("index.php?".SID."&amp;module=config/spiders&amp;action=edit&amp;sid={$spider['sid']}", "post");

	if($errors)
	{
		$page->output_inline_error($errors);
		$spider_data = $mybb->input;
	}
	else
	{
		$spider_data = $spider;
	}

	$form_container = new FormContainer($lang->edit_bot);
	$form_container->output_row($lang->name." <em>*</em>", $lang->name_desc, $form->generate_text_box('name', $spider_data['name'], array('id' => 'name')), 'name');
	$form_container->output_row($lang->user_agent." <em>*</em>", $lang->user_agent_desc, $form->generate_text_box('useragent', $spider_data['useragent'], array('id' => 'useragent')), 'useragent');
	
	$languages = array('' => $lang->use_board_default);
	$languages = array_merge($languages, $lang->get_languages());
	$form_container->output_row($lang->language, $lang->language_desc, $form->generate_select_box("language", $languages, $spider_data['language'], array("id" => "language")), 'language');

	$form_container->output_row($lang->theme, $lang->theme_desc, build_theme_select("theme", $spider_data['theme'], 0, "", 1), 'theme');

	$query = $db->simple_select("usergroups", "*", "", array("order_by" => "title", "order_dir" => "asc"));
	while($usergroup = $db->fetch_array($query))
	{
		$usergroups[$usergroup['gid']] = $usergroup['title'];
	}
	if(!$mybb->input['usergroup'])
	{
		$mybb->input['usergroup'] = 1;
	}
	$form_container->output_row($lang->user_group, $lang->user_group_desc, $form->generate_select_box("usergroup", $usergroups, $mybb->input['usergroup'], array("id" => "usergroup")), 'usergroup');

	$form_container->end();
	$buttons[] = $form->generate_submit_button($lang->save_bot);
	$form->output_submit_wrapper($buttons);
	$form->end();
	
	$page->output_footer();
}

if(!$mybb->input['action'])
{
	$page->output_header($lang->spiders_bots);

	$sub_tabs['spiders'] = array(
		'title' => $lang->spiders_bots,
		'description' => $lang->spiders_bots_desc
	);
	$sub_tabs['add_spider'] = array(
		'title' => $lang->add_new_bot,
		'link' => "index.php?".SID."&amp;module=config/spiders&amp;action=add"
	);

	$page->output_nav_tabs($sub_tabs, "spiders");

	$table = new Table;
	$table->construct_header($lang->bot);
	$table->construct_header($lang->last_visit, array("class" => "align_center", "width" => "200"));
	$table->construct_header($lang->controls, array("class" => "align_center", "width" => 150, "colspan" => 2));

	$query = $db->simple_select("spiders", "*", "", array("order_by" => "lastvisit", "order_dir" => "desc"));
	while($spider = $db->fetch_array($query))
	{
		$spider['name'] = htmlspecialchars_uni($spider['name']);
		if($spider['lastvisit'])
		{
			$lastvisit = my_date($mybb->settings['dateformat'], $spider['lastvisit']).", ".my_date($mybb->settings['timeformat'], $spider['lastvisit']);
		}
		else
		{
			$lastvisit = $lang->never;
		}
		$table->construct_cell($spider['name']);
		$table->construct_cell($lastvisit, array("class" => "align_center"));
		$table->construct_cell("<a href=\"index.php?".SID."&amp;module=config/spiders&amp;action=edit&amp;sid={$spider['sid']}\">{$lang->edit}</a>", array("class" => "align_center"));
		$table->construct_cell("<a href=\"index.php?".SID."&amp;module=config/spiders&amp;action=delete&amp;sid={$spider['sid']}\" onclick=\"return AdminCP.deleteConfirmation(this, '{$lang->confirm_bot_deletion}');\">{$lang->delete}</a>", array("class" => "align_center"));
		$table->construct_row();
	}
	
	if(count($table->rows) == 0)
	{
		$table->construct_cell($lang->no_bots, array("colspan" => 4));
		$table->construct_row();
	}

	$table->output($lang->spiders_bots);

	$page->output_footer();
 }
?>
