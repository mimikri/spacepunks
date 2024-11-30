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

class LanguageBuildCache implements BuildCache
{
	/**
  * @return mixed[]
  */
 public function buildCache(): array
	{
		$languagePath	= ROOT_PATH.'language/';
		
		$languages	= [];
		
		/** @var $fileInfo SplFileObject */
		foreach (new DirectoryIterator($languagePath) as $fileInfo)
		{
			if(!$fileInfo->isDir() || $fileInfo->isDot()) continue;

			$Lang	= $fileInfo->getBasename();

			if(!file_exists($languagePath.$Lang.'/LANG.cfg')) continue;

			// Fixed BOM problems.
			ob_start();
			$path	 = $languagePath.$Lang.'/LANG.cfg';
			require $path;
			ob_end_clean();
			if(isset($Language['name']))
			{
				$languages[$Lang]	= $Language['name'];
			}
		}
		return $languages;
	}
}