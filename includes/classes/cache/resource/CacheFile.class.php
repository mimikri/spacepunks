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

class CacheFile {
	private $path;
	public function __construct()
	{
		$this->path	= is_writable(CACHE_PATH) ? CACHE_PATH : $this->getTempPath();
	}

	private function getTempPath()
	{
		require_once 'includes/libs/wcf/BasicFileUtil.class.php';
		return BasicFileUtil::getTempFolder();
	}

	public function store(string $Key, $Value): int|false {
		return file_put_contents($this->path.'cache.'.$Key.'.php', $Value);
	}
	
	public function open(string $Key): false|string {
		if(!file_exists($this->path.'cache.'.$Key.'.php'))
			return false;
			
		return file_get_contents($this->path.'cache.'.$Key.'.php');
	}
	
	public function flush(string $Key) {
		if(!file_exists($this->path.'cache.'.$Key.'.php'))
			return false;
		
		return unlink($this->path.'cache.'.$Key.'.php');
	}
}