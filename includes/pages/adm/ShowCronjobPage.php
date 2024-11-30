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

if (!allowedTo(str_replace([__DIR__, '\\', '/', '.php'], '', __FILE__))) exit;

function getCronjobTimes($row,$max)
{
	$arr = explode(',',(string) $row);
	if (count($arr) > 1)
		return $arr;
	
	if (substr($arr[0],0,(2-strlen($arr[0]))) == '*/')
		return range(0,$max,(int) substr($arr[0],(2-strlen($arr[0]))));
	else
		return $arr[0];
}

function checkPostData(string $column,$max): false|string
{
	$all = HTTP::_GP($column.'_all', 0);
	if ($all)
		return '*';
	
	$post = HTTP::_GP($column, []);
	$post = array_filter($post, 'is_numeric');
	if (empty($post))
		return false;
	
	$check = [2, 3, 5, 6, 7, 10, 14, 15, 20, 30];
	$result = [];
	foreach ($check as $i)
	{
		if ($i <= $max && range(0, $max, $i) == $post)
			$result[] = $i;
	}
	if (!empty($result))
		return '*/'.max($result);
	else
		return implode(',',$post);
	
}

function ShowCronjob(): void
{
    $cronId = HTTP::_GP('id', 0);

    match (HTTP::_GP('action', 'overview')) {
        'edit' => ShowCronjobEdit($cronId),
        'delete' => ShowCronjobDelete($cronId),
        'lock' => ShowCronjobLock($cronId),
        'unlock' => ShowCronjobUnlock($cronId),
        'detail' => ShowCronjobDetail($cronId),
        'enable' => ShowCronjobEnable($cronId),
        default => ShowCronjobOverview(),
    };
}

function ShowCronjobEdit($post_id): void
{
	global $LNG;

	$post_name 		= 	HTTP::_GP('name', '');
	$post_min 		= 	checkPostData('min', 59);
	$post_hours 	= 	checkPostData('hours', 23);
	$post_month 	= 	checkPostData('month', 12);
	$post_dow 		= 	checkPostData('dow', 6);
	$post_dom 		= 	checkPostData('dom', 31);
	$post_class 	= 	HTTP::_GP('class', '');
	$error_msg 		= 	[];
	
	if ($post_name == '')
		$error_msg[] = $LNG['cronjob_error_name'];
	if ($post_min === false)
		$error_msg[] = $LNG['cronjob_error_min'];
	if ($post_hours === false)
		$error_msg[] = $LNG['cronjob_error_hours'];
	if ($post_month === false)
		$error_msg[] = $LNG['cronjob_error_month'];
	if ($post_dow === false)
		$error_msg[] = $LNG['cronjob_error_dow'];
	if ($post_dom === false)
		$error_msg[] = $LNG['cronjob_error_dom'];
	if ($post_class == '')
		$error_msg[] = $LNG['cronjob_error_class'];
	elseif (!file_exists('includes/classes/cronjob/'.$post_class.'.class.php')) 
		$error_msg[] = $LNG['cronjob_error_filenotfound'].'includes/classes/cronjobs/'.$post_class.'.class.php';
	
	if (count($error_msg) == 0)
	{
		if ($post_id != 0)
			$GLOBALS['DATABASE']->query("UPDATE ".CRONJOBS." SET name = '".$GLOBALS['DATABASE']->sql_escape($post_name)."', min = '".$post_min."', hours = '".$post_hours."', month = '".$post_month."', dow = '".$post_dow."', dom = '".$post_dom."', class = '".$GLOBALS['DATABASE']->sql_escape($post_class)."' WHERE cronjobID = $post_id;");
		else
			$GLOBALS['DATABASE']->query("INSERT INTO ".CRONJOBS." SET name = '".$GLOBALS['DATABASE']->sql_escape($post_name)."', min = '".$post_min."', hours = '".$post_hours."', month = '".$post_month."', dow = '".$post_dow."', dom = '".$post_dom."', class = '".$GLOBALS['DATABASE']->sql_escape($post_class)."';");

		HTTP::redirectTo('admin.php?page=cronjob');
	} else {
		ShowCronjobDetail($post_id,$error_msg);
	}
}

function ShowCronjobDelete(string $cronjobId): void {
    $GLOBALS['DATABASE']->query("DELETE FROM ".CRONJOBS." WHERE cronjobID = ".$cronjobId.";");
    $GLOBALS['DATABASE']->query("DELETE FROM ".CRONJOBS_LOG." WHERE cronjobId = ".$cronjobId.";");
    HTTP::redirectTo('admin.php?page=cronjob');
}

function ShowCronjobLock(string $cronjobId): void {
    $GLOBALS['DATABASE']->query("UPDATE ".CRONJOBS." SET `lock` = MD5(UNIX_TIMESTAMP()) WHERE cronjobID = ".$cronjobId.";");
    HTTP::redirectTo('admin.php?page=cronjob');
}

function ShowCronjobUnlock(string $cronjobId): void {
    $GLOBALS['DATABASE']->query("UPDATE ".CRONJOBS." SET `lock` = NULL WHERE cronjobID = ".$cronjobId.";");
    HTTP::redirectTo('admin.php?page=cronjob');
}

function ShowCronjobEnable(string $cronjobId): void {
    $GLOBALS['DATABASE']->query("UPDATE ".CRONJOBS." SET `isActive` = ".HTTP::_GP('enable', 0)." WHERE cronjobID = ".$cronjobId.";");
    HTTP::redirectTo('admin.php?page=cronjob');
}

function ShowCronjobOverview(): void 
{
	$data    = $GLOBALS['DATABASE']->query("SELECT * FROM ".CRONJOBS.";");

	$template	= new template();	
	if(!$data)
		$template->message($LNG['cronjob_no_data']);
	
	$CronjobArray = [];
	while ($CronjobRow = $GLOBALS['DATABASE']->fetch_array($data))
	{			
		$CronjobArray[]	= ['id'			=> $CronjobRow['cronjobID'], 'isActive'		=> $CronjobRow['isActive'], 'name'			=> $CronjobRow['name'], 'min'			=> $CronjobRow['min'], 'hours'			=> $CronjobRow['hours'], 'dom'			=> $CronjobRow['dom'], 'month'			=> getCronjobTimes($CronjobRow['month'],12), 'dow'			=> getCronjobTimes($CronjobRow['dow'],6), 'class'			=> $CronjobRow['class'], 'nextTime'		=> $CronjobRow['nextTime'], 'lock'			=> !empty($CronjobRow['lock'])];
	}
	$template	= new template();	
	$template->assign_vars(['CronjobArray'	=> $CronjobArray]);
	$template->show("CronjobOverview.tpl");
}

function ShowCronjobDetail(?string $detail,$error_msg=NULL): void 
{
	$template	= new template();
	
	
	$avalibleCrons	= [];
	
	$dir = new DirectoryIterator('includes/classes/cronjob/');
	foreach ($dir as $fileinfo) {
		if ($fileinfo->isFile() && $fileinfo->getBasename('.class.php') != $fileinfo->getFilename()) {
			$avalibleCrons[]	= $fileinfo->getBasename('.class.php');
		}
	}
	
	$template->assign_vars(['avalibleCrons' => $avalibleCrons]);
	
	if ($detail != 0)
	{
		$CronjobRow   	= $GLOBALS['DATABASE']->uniquequery("SELECT * FROM ".CRONJOBS." WHERE cronjobID = ".$detail."");
		$template->assign_vars(['id'			=> $CronjobRow['cronjobID'], 'name'			=> isset($_POST['name'])?HTTP::_GP('name', ''):$CronjobRow['name'], 'min'			=> isset($_POST['min_all'])?[0 => '*']:(isset($_POST['min'])?HTTP::_GP('min', []):getCronjobTimes($CronjobRow['min'],59)), 'hours'			=> isset($_POST['hours_all'])?[0 => '*']:(isset($_POST['hours'])?HTTP::_GP('hours', []):getCronjobTimes($CronjobRow['hours'],23)), 'dom'			=> isset($_POST['dom_all'])?[0 => '*']:(isset($_POST['dom'])?HTTP::_GP('dom', []):getCronjobTimes($CronjobRow['dom'],31)), 'month'			=> isset($_POST['month_all'])?[0 => '*']:(isset($_POST['month'])?HTTP::_GP('month', []):getCronjobTimes($CronjobRow['month'],12)), 'dow'			=> isset($_POST['dow_all'])?[0 => '*']:(isset($_POST['dow'])?HTTP::_GP('dow', []):getCronjobTimes($CronjobRow['dow'],6)), 'class'			=> isset($_POST['class'])?HTTP::_GP('class', ''):$CronjobRow['class'], 'error_msg'		=> $error_msg]);
	} else {
		$template->assign_vars(['id'			=> 'add', 'name'			=> HTTP::_GP('name', ''), 'min'			=> isset($_POST['min_all'])?[0 => '*']:HTTP::_GP('min', []), 'hours'			=> isset($_POST['hours_all'])?[0 => '*']:HTTP::_GP('hours', []), 'dom'			=> isset($_POST['dom_all'])?[0 => '*']:HTTP::_GP('dom', []), 'month'			=> isset($_POST['month_all'])?[0 => '*']:HTTP::_GP('month', []), 'dow'			=> isset($_POST['dow_all'])?[0 => '*']:HTTP::_GP('dow', []), 'class'			=> HTTP::_GP('class', ''), 'error_msg'		=> $error_msg]);
	}
	$template->show("CronjobDetail.tpl");
}

