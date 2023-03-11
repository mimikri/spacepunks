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

function ShowIndexPage()
{
	global $LNG;
	$template	= new template();

	$template->assign_vars(array(	
		'game_name'		=> Config::get()->game_name,
		'adm_cp_title'	=> $LNG['adm_cp_title'],
	));
	
	$template->display('adm/ShowIndexPage.tpl');
}