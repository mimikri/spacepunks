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

function ShowModulePage(): void
{
	global $LNG;

	$config	= Config::get(Universe::getEmulated());
	$module	= explode(';', $config->moduls);
	
	if(!empty($_GET['mode'])) {
		$module[HTTP::_GP('id', 0)]	= ($_GET['mode'] == 'aktiv') ? 1 : 0;
		$config->moduls = implode(";", $module);
		$config->save();
		ClearCache();
	}
	
	$IDs	= range(0, MODULE_AMOUNT - 1);
	foreach($IDs as $ID => $Name) {
		$Modules[$ID]	= ['name'	=> $LNG['modul_'.$ID], 'state'	=> $module[$ID] ?? 1];
	}
	
	asort($Modules);
	$template	= new template();

	$template->assign_vars(['Modules'				=> $Modules, 'mod_module'			=> $LNG['mod_module'], 'mod_info'				=> $LNG['mod_info'], 'mod_active'			=> $LNG['mod_active'], 'mod_deactive'			=> $LNG['mod_deactive'], 'mod_change_active'		=> $LNG['mod_change_active'], 'mod_change_deactive'	=> $LNG['mod_change_deactive']]);
	
	$template->show('ModulePage.tpl');
}