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

class ShowBattleHallPage extends AbstractGamePage
{
	public static $requireModule = MODULE_BATTLEHALL;

	function __construct()
    {
		parent::__construct();
	}

	function show(): void
	{
		global $USER, $LNG;
		$order  = HTTP::_GP('order', 'units');
		$sort   = HTTP::_GP('sort', 'desc');
		$sort   = strtoupper((string) $sort) === "DESC" ? "DESC" : "ASC";


		$key = match ($order) {
      'date' => '%%TOPKB%%.time '.$sort,
      default => '%%TOPKB%%.units '.$sort,
  };

		$db = Database::get();
		$sql = "SELECT *, (
			SELECT DISTINCT
			IF(%%TOPKB_USERS%%.username = '', GROUP_CONCAT(%%USERS%%.username SEPARATOR ' & '), GROUP_CONCAT(%%TOPKB_USERS%%.username SEPARATOR ' & '))
			FROM %%TOPKB_USERS%%
			LEFT JOIN %%USERS%% ON uid = %%USERS%%.id
			WHERE %%TOPKB_USERS%%.rid = %%TOPKB%%.rid AND role = 1
		) as attacker,
		(
			SELECT DISTINCT
			IF(%%TOPKB_USERS%%.username = '', GROUP_CONCAT(%%USERS%%.username SEPARATOR ' & '), GROUP_CONCAT(%%TOPKB_USERS%%.username SEPARATOR ' & '))
			FROM %%TOPKB_USERS%% INNER JOIN %%USERS%% ON uid = id
			WHERE %%TOPKB_USERS%%.rid = %%TOPKB%%.`rid` AND `role` = 2
		) as defender
		FROM %%TOPKB%% WHERE universe = :universe ORDER BY ".$key." LIMIT 100;";

		$top = $db->select($sql, [':universe' => Universe::current()]);

		$TopKBList	= [];
		foreach($top as $data)
		{
			$TopKBList[]	= ['result'	=> $data['result'], 'date'		=> _date($LNG['php_tdformat'], $data['time'], $USER['timezone']), 'time'		=> TIMESTAMP - $data['time'], 'units'		=> $data['units'], 'rid'		=> $data['rid'], 'attacker'	=> $data['attacker'], 'defender'	=> $data['defender']];
		}

		$this->assign(['TopKBList'		=> $TopKBList, 'sort'			=> $sort, 'order'			=> $order]);

		$this->display('page.battleHall.default.tpl');
	}
}