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


class ShowFleetDealerPage extends AbstractGamePage
{
	public static $requireModule = MODULE_FLEET_TRADER;

	function __construct() 
	{
		parent::__construct();
	}
	
	public function send(): void
	{
		global $USER, $PLANET, $LNG, $pricelist, $resource;
		
		$shipID			= HTTP::_GP('shipID', 0);
		$Count			= max(0, round(HTTP::_GP('count', 0.0)));
		$allowedShipIDs	= explode(',', Config::get()->trade_allowed_ships);
		
		if(!empty($shipID) && !empty($Count) && in_array($shipID, $allowedShipIDs) && $PLANET[$resource[$shipID]] >= $Count)
		{
			$tradeCharge					= 1 - (Config::get()->trade_charge / 100);
			$PLANET[$resource[901]]			+= $Count * $pricelist[$shipID]['cost'][901] * $tradeCharge;
			$PLANET[$resource[902]]			+= $Count * $pricelist[$shipID]['cost'][902] * $tradeCharge;
			$PLANET[$resource[903]]			+= $Count * $pricelist[$shipID]['cost'][903] * $tradeCharge;
			$USER[$resource[921]]			+= $Count * $pricelist[$shipID]['cost'][921] * $tradeCharge;
			
			$PLANET[$resource[$shipID]]		-= $Count;

            $sql = 'UPDATE %%PLANETS%% SET '.$resource[$shipID].' = '.$resource[$shipID].' - :count WHERE id = :planetID;';
			Database::get()->update($sql, [':count'        => $Count, ':planetID'     => $PLANET['id']]);

            $this->printMessage($LNG['tr_exchange_done'], [['label'	=> $LNG['sys_forward'], 'url'	=> 'game.php?page=fleetDealer']]);
		}
		else
		{
			$this->printMessage($LNG['tr_exchange_error'], [['label'	=> $LNG['sys_back'], 'url'	=> 'game.php?page=fleetDealer']]);
		}
		
	}
	
	function show(): void
	{
		global $PLANET, $LNG, $pricelist, $resource, $reslist;
		
		$Cost		= [];
		
		$allowedShipIDs	= explode(',', Config::get()->trade_allowed_ships);
		
		foreach($allowedShipIDs as $shipID)
		{
			if(in_array($shipID, $reslist['fleet']) || in_array($shipID, $reslist['defense'])) {
				$Cost[$shipID]	= [$PLANET[$resource[$shipID]], $LNG['tech'][$shipID], $pricelist[$shipID]['cost']];
			}
		}
		
		if(empty($Cost))
		{
			$this->printMessage($LNG['ft_empty'], [['label'	=> $LNG['sys_back'], 'url'	=> 'game.php?page=fleetDealer']]);
		}

		$this->assign(['shipIDs'	=> $allowedShipIDs, 'CostInfos'	=> $Cost, 'Charge'	=> Config::get()->trade_charge]);
		
		$this->display('page.fleetDealer.default.tpl');
	}
}