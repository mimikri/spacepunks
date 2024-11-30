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

if (!allowedTo(str_replace([__DIR__, '\\', '/', '.php'], '', __FILE__))) throw new Exception("Permission error!");

function ShowStatUpdatePage(): void {
	global $LNG;
	require_once('includes/classes/class.statbuilder.php');
	$stat			= new statbuilder();
	$result			= $stat->MakeStats();
	$memory_p		= str_replace(["%p", "%m"], $result['memory_peak'], $LNG['sb_top_memory']);
	$memory_e		= str_replace(["%e", "%m"], $result['end_memory'], $LNG['sb_final_memory']);
	$memory_i		= str_replace(["%i", "%m"], $result['initial_memory'], $LNG['sb_start_memory']);
	$stats_end_time	= 'Updated in: '. $result['totaltime'] .'s<br>';
	$stats_sql		= sprintf($LNG['sb_sql_counts'], $result['sql_count']);

	$template = new template();
	$template->message($LNG['sb_stats_updated'].$stats_end_time.$memory_i.$memory_e.$memory_p.$stats_sql, false, 0, true);
}
