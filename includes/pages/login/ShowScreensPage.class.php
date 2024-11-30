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


class ShowScreensPage extends AbstractLoginPage
{
	public static $requireModule = 0;

	function __construct() 
	{
		parent::__construct();
	}
	
	function show(): void 
	{
		$screenshots	= [];
		$directoryIterator = new DirectoryIterator('styles/resource/images/login/screens/');
        foreach ($directoryIterator as $fileInfo)
		{
			/** @var $fileInfo DirectoryIterator */
			if (!$fileInfo->isFile())
			{
				continue;
            }			
			
			$thumbnail = 'styles/resource/images/login/screens/'.$fileInfo->getFilename();
			if(file_exists('styles/resource/images/login/screens/thumbnails/'.$fileInfo->getFilename()))
			{
				$thumbnail = 'styles/resource/images/login/screens/thumbnails/'.$fileInfo->getFilename();
			}
			
			$screenshots[]	= ['path' 		=> 'styles/resource/images/login/screens/'.$fileInfo->getFilename(), 'thumbnail' => $thumbnail];
		}
		
		$this->assign(['screenshots' => $screenshots]);

		$this->display('page.screens.default.tpl');
	}
}
