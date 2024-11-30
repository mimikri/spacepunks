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

class HTTPRequest
{
	private $content	= NULL;
	private $ch			= NULL;

	public function __construct(private $url = NULL)
 {
 }

	public function send(): void
	{
		if(function_exists("curl_init"))
		{
			$this->ch	= curl_init($this->url);
			curl_setopt($this->ch, CURLOPT_HTTPGET, true);
			curl_setopt($this->ch, CURLOPT_AUTOREFERER, true);
			curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($this->ch, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; spacepunks/".Config::get()->VERSION."; +https://2moons.de)");
			curl_setopt($this->ch, CURLOPT_HTTPHEADER, ["Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8", "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.3", "Accept-Language: de-DE,de;q=0.8,en-US;q=0.6,en;q=0.4"]);
			
			$this->content	= curl_exec($this->ch);
			curl_close($this->ch);
		}
	}
	
	public function getResponse()
	{
		$this->send();
		return $this->content;
	}
}