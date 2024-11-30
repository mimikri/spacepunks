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

function ShowPassEncripterPage(): void
{
	global $LNG;
	$Password	= HTTP::_GP('md5q', '', true);
	
	$template	= new template();

	$template->assign_vars(['md5_md5' 			=> $Password, 'md5_enc' 			=> PlayerUtil::cryptPassword($Password), 'et_md5_encripter' 	=> $LNG['et_md5_encripter'], 'et_encript' 		=> $LNG['et_encript'], 'et_result' 		=> $LNG['et_result'], 'et_pass' 			=> $LNG['et_pass']]);
	
	$template->show('PassEncripterPage.tpl');
}