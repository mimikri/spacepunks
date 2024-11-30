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


class ShowErrorPage extends AbstractGamePage
{
	public static $requireModule = 0;
	
	protected $disableEcoSystem = true;

	function __construct() 
	{
		parent::__construct();
		$this->initTemplate();
	}
	
	static function printError($Message, $fullSide = true, $redirect = NULL): void
	{
		$pageObj	= new self;
		$pageObj->printMessage($Message, $fullSide, $redirect);
	}
	
	function show() 
	{
		
	}
}
