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

function ShowDisclamerPage()
{
	global $LNG;


	$config = Config::get(Universe::getEmulated());

	if (!empty($_POST))
	{
		$config_before = array(	
			'disclamerAddress'	=> $config->disclamerAddress,
			'disclamerPhone'	=> $config->disclamerPhone,
			'disclamerMail'	=> $config->disclamerMail,
			'disclamerNotice'	=> $config->disclamerNotice,
		);
		
		$disclaimerAddress	= HTTP::_GP('disclaimerAddress', '', true);
		$disclaimerPhone	= HTTP::_GP('disclaimerPhone', '', true);
		$disclaimerMail		= HTTP::_GP('disclaimerMail', '', true);
		$disclaimerNotice	= HTTP::_GP('disclaimerNotice', '', true);
		
		$config_after = array(	
			'disclamerAddress'	=> $disclaimerAddress,
			'disclamerPhone'	=> $disclaimerPhone,
			'disclamerMail'		=> $disclaimerMail,
			'disclamerNotice'	=> $disclaimerNotice,
		);

		foreach($config_after as $key => $value)
		{
			$config->$key	= $value;
		}
		$config->save();
		
		$LOG = new Log(3);
		$LOG->target = 5;
		$LOG->old = $config_before;
		$LOG->new = $config_after;
		$LOG->save();
	}

	$template	= new template();
	$template->loadscript('../base/jquery.autosize-min.js');
	$template->execscript('$(\'textarea\').autosize();');

	$template->assign_vars(array(
		'disclaimerAddress'		=> $config->disclamerAddress,
		'disclaimerPhone'		=> $config->disclamerPhone,
		'disclaimerMail'		=> $config->disclamerMail,
		'disclaimerNotice'		=> $config->disclamerNotice,
		'se_server_parameters'	=> $LNG['mu_disclaimer'],
		'se_save_parameters'	=> $LNG['se_save_parameters'],
		'se_disclaimerAddress'	=> $LNG['se_disclaimerAddress'],
		'se_disclaimerPhone'	=> $LNG['se_disclaimerPhone'],
		'se_disclaimerMail'		=> $LNG['se_disclaimerMail'],
		'se_disclaimerNotice'	=> $LNG['se_disclaimerNotice'],
	));
	
	$template->show('DisclamerConfigBody.tpl');
}
