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


class ShowTraderPage extends AbstractGamePage
{
	public static $requireModule = MODULE_TRADER;

	function __construct() 
	{
		parent::__construct();
	}
	
	public static $Charge = [901	=> [901 => 1, 902 => 2, 903 => 4], 902	=> [901 => 0.5, 902 => 1, 903 => 2], 903	=> [901 => 0.25, 902 => 0.5, 903 => 1]];
	
	public function show(): void 
	{
		global $LNG, $USER, $resource;

		$darkmatter_cost_trader	= Config::get()->darkmatter_cost_trader;

		$this->assign(['tr_cost_dm_trader'		=> sprintf($LNG['tr_cost_dm_trader'], pretty_number($darkmatter_cost_trader), $LNG['tech'][921]), 'charge'				=> self::$Charge, 'resource'				=> $resource, 'requiredDarkMatter'	=> $USER['darkmatter'] < $darkmatter_cost_trader ? sprintf($LNG['tr_not_enought'], $LNG['tech'][921]) : false]);
		
		$this->display("page.trader.default.tpl");
	}
		
	function trade(): void
	{
		global $USER, $LNG;
		
		if ($USER['darkmatter'] < Config::get()->darkmatter_cost_trader) {
			$this->redirectTo('game.php?page=trader');
		}
		
		$resourceID	= HTTP::_GP('resource', 0);
		
		if(!in_array($resourceID, array_keys(self::$Charge))) {
			$this->printMessage($LNG['invalid_action'], [['label'	=> $LNG['sys_back'], 'url'	=> 'game.php?page=trader']]);
		}
		
		$tradeResources	= array_values(array_diff(array_keys(self::$Charge[$resourceID]), [$resourceID]));
		$this->tplObj->loadscript("trader.js");
		$this->assign(['tradeResourceID'	=> $resourceID, 'tradeResources'	=> $tradeResources, 'charge' 			=> self::$Charge[$resourceID]]);

		$this->display('page.trader.trade.tpl');
	}
	
	function send(): void
	{
		global $USER, $PLANET, $LNG, $resource;
		
		if ($USER['darkmatter'] < Config::get()->darkmatter_cost_trader) {
			$this->redirectTo('game.php?page=trader');
		}
		
		$resourceID	= HTTP::_GP('resource', 0);
		
		if(!in_array($resourceID, array_keys(self::$Charge))) {
			$this->printMessage($LNG['invalid_action'], [['label'	=> $LNG['sys_back'], 'url'	=> 'game.php?page=trader']]);
		}

		$getTradeResources	= HTTP::_GP('trade', []);
		
		$tradeResources		= array_values(array_diff(array_keys(self::$Charge[$resourceID]), [$resourceID]));
		$tradeSum 			= 0;
		
		foreach($tradeResources as $tradeRessID)
		{
			if(!isset($getTradeResources[$tradeRessID]))
			{
				continue;
			}
			$tradeAmount	= max(0, round((float) $getTradeResources[$tradeRessID]));
			
			if(empty($tradeAmount) || !isset(self::$Charge[$resourceID][$tradeRessID]))
			{
				continue;  
			}
			
			if(isset($PLANET[$resource[$resourceID]]))
			{
				$usedResources	= $tradeAmount * self::$Charge[$resourceID][$tradeRessID];
				
				if($usedResources > $PLANET[$resource[$resourceID]])
				{
					$this->printMessage(sprintf($LNG['tr_not_enought'], $LNG['tech'][$resourceID]), [['label'	=> $LNG['sys_back'], 'url'	=> 'game.php?page=trader']]);
				}
				
				$tradeSum	  						+= $tradeAmount;
				$PLANET[$resource[$resourceID]]		-= $usedResources;
			}
			elseif(isset($USER[$resource[$resourceID]]))
			{
				if($resourceID == 921)
				{
					$USER[$resource[$resourceID]]	-= Config::get()->darkmatter_cost_trader;
				}
				
				$usedResources	= $tradeAmount * self::$Charge[$resourceID][$tradeRessID];
				
				if($usedResources > $USER[$resource[$resourceID]])
				{
					$this->printMessage(sprintf($LNG['tr_not_enought'], $LNG['tech'][$resourceID]), [['label'	=> $LNG['sys_back'], 'url'	=> 'game.php?page=trader']]);
				}
				
				$tradeSum	  						+= $tradeAmount;
				$USER[$resource[$resourceID]]		-= $usedResources;
				
				if($resourceID == 921)
				{
					$USER[$resource[$resourceID]]	+= Config::get()->darkmatter_cost_trader;
				}
			}
			else
			{
				throw new Exception('Unknown resource ID #'.$resourceID);
			}
			
			if(isset($PLANET[$resource[$tradeRessID]]))
			{
				$PLANET[$resource[$tradeRessID]]	+= $tradeAmount;
			}
			elseif(isset($USER[$resource[$tradeRessID]]))
			{
				$USER[$resource[$tradeRessID]]		+= $tradeAmount;
			}
			else
			{
				throw new Exception('Unknown resource ID #'.$tradeRessID);
			}
		}
		
		if ($tradeSum > 0)
		{
			$USER[$resource[921]]	-= Config::get()->darkmatter_cost_trader;
		}
		
		$this->printMessage($LNG['tr_exchange_done'], [['label'	=> $LNG['sys_forward'], 'url'	=> 'game.php?page=trader']]);
	}
}