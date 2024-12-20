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

class ShowBattleHallPage extends AbstractLoginPage
{
	public static $requireModule = 0;

	function __construct() 
	{
		parent::__construct();
	}
	
	function show(): void 
	{
		global $LNG;
		$db = Database::get();

		$sql = "SELECT *, (
			SELECT DISTINCT
			IF(%%TOPKB_USERS%%.username = '', GROUP_CONCAT(%%USERS%%.username SEPARATOR ' & '), GROUP_CONCAT(%%TOPKB_USERS%%.username SEPARATOR ' & '))
			FROM %%TOPKB_USERS%%
			LEFT JOIN %%USERS%% ON uid = %%USERS%%.id
			WHERE %%TOPKB_USERS%%.`rid` = %%TOPKB%%.`rid` AND `role` = 1
		) as `attacker`,
		(
			SELECT DISTINCT
			IF(%%TOPKB_USERS%%.username = '', GROUP_CONCAT(%%USERS%%.username SEPARATOR ' & '), GROUP_CONCAT(%%TOPKB_USERS%%.username SEPARATOR ' & '))
			FROM %%TOPKB_USERS%% INNER JOIN %%USERS%% ON uid = id
			WHERE %%TOPKB_USERS%%.`rid` = %%TOPKB%%.`rid` AND `role` = 2
		) as `defender`
		FROM %%TOPKB%% WHERE `universe` = :universe ORDER BY units DESC LIMIT 100;";

		$hallRaw = $db->select($sql, [':universe'	=> Universe::current()]);

		$hallList	= [];
		foreach($hallRaw as $hallRow) {
			$hallList[]	= ['result'	=> $hallRow['result'], 'time'		=> _date($LNG['php_tdformat'], $hallRow['time']), 'units'		=> $hallRow['units'], 'rid'		=> $hallRow['rid'], 'attacker'	=> $hallRow['attacker'], 'defender'	=> $hallRow['defender']];
		}

		$universeSelect	= $this->getUniverseSelector();
		
		$this->assign(['universeSelect'	=> $universeSelect, 'hallList'			=> $hallList]);
		$this->display('page.battleHall.default.tpl');
	}
}