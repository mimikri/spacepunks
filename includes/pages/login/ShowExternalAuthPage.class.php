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

class ShowExternalAuthPage extends AbstractLoginPage
{
	public static $requireModule = 0;

	function __construct() 
	{
		parent::__construct();
	}
	
	function show(): void 
	{
		$method			= HTTP::_GP('method', '');
		$method			= strtolower(str_replace(['_', '\\', '/', '.', "\0"], '', $method));
		$path			= 'includes/classes/extauth/'.$method.'.class.php';
		
		if(!file_exists($path)) {
			HTTP::redirectTo('index.php');			
		}

		$session	= Session::create();

		require 'includes/classes/extauth/externalAuth.interface.php';
		require $path;
		
		$methodClass	= ucwords($method).'Auth';

		/** @var $authObj externalAuth */
		$authObj		= new $methodClass;
		
		if(!$authObj->isActiveMode())
		{
			$session->delete();
			$this->redirectTo('index.php?code=5');
		}
		
		if(!$authObj->isValid())
		{
			$session->delete();
			$this->redirectTo('index.php?code=4');
		}
		
		$loginData	= $authObj->getLoginData();
		
		if(empty($loginData))
		{
			$session->delete();
			$this->redirectTo('index.php?page=register&externalAuth[account]='.$authObj->getAccount().'&externalAuth[method]='.$method);
		}

		$session->userId		= (int) $loginData['id'];
		$session->adminAccess	= 0;
		$session->save();
		$this->redirectTo("game.php");	
	}
}