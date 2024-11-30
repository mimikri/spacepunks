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

function ShowTeamspeakPage(): void {
	global $LNG;


	$config = Config::get(Universe::getEmulated());

	if ($_POST)
	{
		$config_before = ['ts_timeout'		=> $config->ts_timeout, 'ts_modon'			=> $config->ts_modon, 'ts_server'			=> $config->ts_server, 'ts_tcpport'		=> $config->ts_tcpport, 'ts_udpport'		=> $config->ts_udpport, 'ts_version'		=> $config->ts_version, 'ts_login'			=> $config->ts_login, 'ts_password'		=> $config->ts_password, 'ts_cron_interval'	=> $config->ts_cron_interval];
		
		$ts_modon 			= isset($_POST['ts_on']) && $_POST['ts_on'] == 'on' ? 1 : 0;		
		$ts_server			= HTTP::_GP('ts_ip', '');
		$ts_tcpport			= HTTP::_GP('ts_tcp', 0);
		$ts_udpport			= HTTP::_GP('ts_udp', 0);
		$ts_timeout			= HTTP::_GP('ts_to', 0);
		$ts_version			= HTTP::_GP('ts_v', 0);
		$ts_login			= HTTP::_GP('ts_login', '');
		$ts_password		= HTTP::_GP('ts_password', '', true);
		$ts_cron_interval	= HTTP::_GP('ts_cron', 0);
		
		$config_after = ['ts_timeout'		=> $ts_timeout, 'ts_modon'			=> $ts_modon, 'ts_server'			=> $ts_server, 'ts_tcpport'		=> $ts_tcpport, 'ts_udpport'		=> $ts_udpport, 'ts_version'		=> $ts_version, 'ts_login'			=> $ts_login, 'ts_password'		=> $ts_password, 'ts_cron_interval'	=> $ts_cron_interval];


		foreach($config_after as $key => $value)
		{
			$config->$key	= $value;
		}

		$config->save();

		$sql	= "UPDATE %%CRONJOBS%%
		SET isActive = :isActive, `lock` = NULL, nextTime = 0
		WHERE name = 'teamspeak';";

		Database::get()->update($sql, [':isActive'	=> $ts_modon]);
		
		$LOG = new Log(3);
		$LOG->target = 4;
		$LOG->old = $config_before;
		$LOG->new = $config_after;
		$LOG->save();
		
	}
	$template	= new template();
	

	$template->assign_vars(['se_save_parameters'	=> $LNG['se_save_parameters'], 'ts_tcpport'			=> $LNG['ts_tcpport'], 'ts_serverip'			=> $LNG['ts_serverip'], 'ts_version'			=> $LNG['ts_version'], 'ts_active'				=> $LNG['ts_active'], 'ts_settings'			=> $LNG['ts_settings'], 'ts_udpport'			=> $LNG['ts_udpport'], 'ts_timeout'			=> $LNG['ts_timeout'], 'ts_server_query'		=> $LNG['ts_server_query'], 'ts_sq_login'			=> $LNG['ts_login'], 'ts_sq_pass'			=> $LNG['ts_pass'], 'ts_lng_cron'			=> $LNG['ts_cron'], 'ts_to'					=> $config->ts_timeout, 'ts_on'					=> $config->ts_modon, 'ts_ip'					=> $config->ts_server, 'ts_tcp'				=> $config->ts_tcpport, 'ts_udp'				=> $config->ts_udpport, 'ts_v'					=> $config->ts_version, 'ts_login'				=> $config->ts_login, 'ts_password'			=> $config->ts_password, 'ts_cron'				=> $config->ts_cron_interval]);
	$template->show('TeamspeakPage.tpl');

}