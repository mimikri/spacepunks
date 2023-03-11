<?php

/**
 *  Spacepunks
 *   2moons by Jan-Otto Kröpke 2009-2016
 *   Spacepunks by mimikri 2023
 *
 * For the full copyright and license information, please view the LICENSE
 *
 * @package Spacepunks
 * @author mimikri
 * @copyright 2009 Lucky
 * @copyright 2016 Jan-Otto Kröpke <slaver7@gmail.com>
 * @copyright 2023 mimikri
 * @licence MIT
 * @version 0.0.1
 * @link https://github.com/mimikri/spacepunks
 */

if (!allowedTo(str_replace(array(dirname(__FILE__), '\\', '/', '.php'), '', __FILE__))) throw new Exception("Permission error!");

function ShowNewsPage(){
	global $LNG, $USER;
	$get_action = empty($_GET['action']) ? '' : $_GET['action'];
	if($get_action == 'send') {
		$edit_id 	= HTTP::_GP('id', 0);
		$title 		= $GLOBALS['DATABASE']->sql_escape(HTTP::_GP('title', '', true));
		$text 		= $GLOBALS['DATABASE']->sql_escape(HTTP::_GP('text', '', true));
		$query		= ($_GET['mode'] == 2) ? "INSERT INTO ".NEWS." (`id` ,`user` ,`date` ,`title` ,`text`) VALUES ( NULL , '".$USER['username']."', '".TIMESTAMP."', '".$title."', '".$text."');" : "UPDATE ".NEWS." SET `title` = '".$title."', `text` = '".$text."', `date` = '".TIMESTAMP."' WHERE `id` = '".$edit_id."' LIMIT 1;";
		
		$GLOBALS['DATABASE']->query($query);
	} elseif($get_action == 'delete' && isset($_GET['id'])) {
		$GLOBALS['DATABASE']->query("DELETE FROM ".NEWS." WHERE `id` = '".HTTP::_GP('id', 0)."';");
	}

	$query = $GLOBALS['DATABASE']->query("SELECT * FROM ".NEWS." ORDER BY id ASC");
	$NewsList = array();
	while ($u = $GLOBALS['DATABASE']->fetch_array($query)) {
		$NewsList[]	= array(
			'id'		=> $u['id'],
			'title'		=> $u['title'],
			'date'		=> _date($LNG['php_tdformat'], $u['date'], $USER['timezone']),
			'user'		=> $u['user'],
			'confirm'	=> sprintf($LNG['nws_confirm'], $u['title']),
		);
	}
	
	$template	= new template();


	if($get_action== 'edit' && isset($_GET['id'])) {
		$News = $GLOBALS['DATABASE']->getFirstRow("SELECT id, title, text FROM ".NEWS." WHERE id = '".$GLOBALS['DATABASE']->sql_escape($_GET['id'])."';");
		$template->assign_vars(array(	
			'mode'			=> 1,
			'nws_head'		=> sprintf($LNG['nws_head_edit'], $News['title']),
			'news_id'		=> $News['id'],
			'news_title'	=> $News['title'],
			'news_text'		=> $News['text'],
		));
	} elseif($get_action == 'create') {
		$template->assign_vars(array(	
			'mode'			=> 2,
			'nws_head'		=> $LNG['nws_head_create'],
		));
	}
	
	$template->assign_vars(array(	
		'NewsList'		=> $NewsList,
		'button_submit'	=> $LNG['button_submit'],
		'nws_total'		=> sprintf($LNG['nws_total'], count($NewsList)),
		'nws_news'		=> $LNG['nws_news'],
		'nws_id'		=> $LNG['nws_id'],
		'nws_title'		=> $LNG['nws_title'],
		'nws_date'		=> $LNG['nws_date'],
		'nws_from'		=> $LNG['nws_from'],
		'nws_del'		=> $LNG['nws_del'],
		'nws_create'	=> $LNG['nws_create'],
		'nws_content'	=> $LNG['nws_content'],
	));
	
	$template->show('NewsPage.tpl');
}