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

class StatBanner {

	private string $source = "styles/resource/images/banner.jpg";

	public function GetData($id)
	{
		$sql = 'SELECT user.username, user.wons, user.loos, user.draws,
		stat.total_points, stat.total_rank,
		planet.name, planet.galaxy, planet.system, planet.planet, config.game_name,
		config.users_amount, config.ttf_file
		FROM %%USERS%% as user, %%STATPOINTS%% as stat, %%PLANETS%% as planet, %%CONFIG%% as config
		WHERE user.id = :userId AND stat.stat_type = :statType AND stat.id_owner = :userId
		AND planet.id = user.id_planet AND config.uni = user.universe;';

		return Database::get()->selectSingle($sql, [':userId'	=> $id, ':statType'	=> 1]);
	}
	
	public function CreateUTF8Banner(array $data): void {
		global $LNG;
		$image  	= imagecreatefromjpeg($this->source);

		$Font		= $data['ttf_file'];
		if(!file_exists($Font))
			$this->BannerError('TTF Font missing!');
			
		// Colors		
		$color	= imagecolorallocate($image, 255, 255, 225);
		$shadow = imagecolorallocate($image, 33, 33, 33);
		
		$total	= $data['wons'] + $data['loos'] + $data['draws'];
		
		$quote	= $total != 0 ? $data['wons'] / $total * 100 : 0;
		
		// Username
		imagettftext($image, 20, 0, 20, 31, $shadow, $Font, (string) $data['username']);
		imagettftext($image, 20, 0, 20, 30, $color, $Font, (string) $data['username']);
		
		imagettftext($image, 16, 0, 250, 31, $shadow, $Font, (string) $data['game_name']);
		imagettftext($image, 16, 0, 250, 30, $color, $Font, (string) $data['game_name']);
		
		imagettftext($image, 11, 0, 20, 60, $shadow, $Font, $LNG['ub_rank'].': '.$data['total_rank']);
		imagettftext($image, 11, 0, 20, 59, $color, $Font, $LNG['ub_rank'].': '.$data['total_rank']);
		
		imagettftext($image, 11, 0, 20, 81, $shadow, $Font, $LNG['ub_points'].': '.html_entity_decode(shortly_number($data['total_points'])));
		imagettftext($image, 11, 0, 20, 80, $color, $Font, $LNG['ub_points'].': '.html_entity_decode(shortly_number($data['total_points'])));
		
		imagettftext($image, 11, 0, 250, 60, $shadow, $Font, $LNG['ub_fights'].': '.html_entity_decode(shortly_number($total, 0)));
		imagettftext($image, 11, 0, 250, 59, $color, $Font, $LNG['ub_fights'].': '.html_entity_decode(shortly_number($total, 0)));
		
		imagettftext($image, 11, 0, 250, 81, $shadow, $Font, $LNG['ub_quote'].': '.html_entity_decode(shortly_number($quote, 2)).'%');
		imagettftext($image, 11, 0, 250, 80, $color, $Font, $LNG['ub_quote'].': '.html_entity_decode(shortly_number($quote, 2)).'%');
		
		if(!isset($_GET['debug']))
		{
			HTTP::sendHeader('Content-type', 'image/jpg');
		}

		imagejpeg($image);
		imagedestroy($image);
	}
	
	function BannerError($Message): never {
		HTTP::sendHeader('Content-type', 'image/jpg');
		$im	 = imagecreate(450, 80);
		$text_color = imagecolorallocate($im, 233, 14, 91);
		imagestring($im, 3, 5, 5, (string) $Message, $text_color);
		imagejpeg($im);
		imagedestroy($im);
		exit;
	}
}