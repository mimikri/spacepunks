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

$token	= getRandomString();
$db		= Database::get();

$fleetResult	= $db->update("UPDATE %%FLEETS_EVENT%% SET `lock` = :token WHERE `lock` IS NULL AND `time` <= :time;", [':time'		=> TIMESTAMP, ':token'	=> $token]);

if($db->rowCount() !== 0) {
	require 'includes/classes/class.FlyingFleetHandler.php';
	
	$fleetObj	= new FlyingFleetHandler();
	$fleetObj->setToken($token);
	$fleetObj->run();

	$db->update("UPDATE %%FLEETS_EVENT%% SET `lock` = NULL WHERE `lock` = :token;", [':token' => $token]);
}