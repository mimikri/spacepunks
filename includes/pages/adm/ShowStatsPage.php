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

function ShowStatsPage(): void 
{
	global $LNG;

	$config = Config::get(Universe::getEmulated());

	if ($_POST)
	{
		$config_before = ['stat_settings' 	=> $config->stat_settings, 'stat' 				=> $config->stat, 'stat_level' 		=> $config->stat_level];
		
		$stat_settings				= HTTP::_GP('stat_settings', 0);
		$stat 						= HTTP::_GP('stat', 0);
		$stat_level					= HTTP::_GP('stat_level', 0);
		
		$config_after = ['stat_settings'		=> $stat_settings, 'stat'				=> $stat, 'stat_level' 		=> $stat_level];

		foreach($config_after as $key => $value)
		{
			$config->$key	= $value;
		}
		$config->save();
		
		$LOG = new Log(3);
		$LOG->target = 2;
		$LOG->old = $config_before;
		$LOG->new = $config_after;
		$LOG->save();
	}
	
	$template	= new template();


	$template->assign_vars(['stat_level'						=> $config->stat_level, 'stat'								=> $config->stat, 'stat_settings'						=> $config->stat_settings, 'cs_access_lvl'						=> $LNG['cs_access_lvl'], 'cs_points_to_zero'					=> $LNG['cs_points_to_zero'], 'cs_point_per_resources_used'		=> $LNG['cs_point_per_resources_used'], 'cs_title'							=> $LNG['cs_title'], 'cs_resources'						=> $LNG['cs_resources'], 'cs_save_changes'					=> $LNG['cs_save_changes'], 'Selector'							=> [1 => $LNG['cs_yes'], 2 => $LNG['cs_no_view'], 0 => $LNG['cs_no']]]);
		
	$template->show('StatsPage.tpl');
}