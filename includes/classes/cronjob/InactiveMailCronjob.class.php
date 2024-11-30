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

require_once 'includes/classes/cronjob/CronjobTask.interface.php';

class InactiveMailCronjob
{
	function run(): void
	{
		global $LNG;

		$config	= Config::get(ROOT_UNI);
		
		if($config->mail_active == 1) {
			/** @var $langObjects Language[] */
			$langObjects	= [];
		
			require 'includes/classes/Mail.class.php';

			$sql	= 'SELECT `id`, `username`, `lang`, `email`, `onlinetime`, `timezone`, `universe`
			FROM %%USERS%% WHERE `inactive_mail` = 0 AND `onlinetime` < :time;';

			$inactiveUsers	= Database::get()->select($sql, [':time'	=> TIMESTAMP - $config->del_user_sendmail * 24 * 60 * 60]);

			foreach($inactiveUsers as $user)
			{
				if(!isset($langObjects[$user['lang']]))
				{
					$langObjects[$user['lang']]	= new Language($user['lang']);
					$langObjects[$user['lang']]->includeData(['L18N', 'INGAME', 'PUBLIC', 'CUSTOM']);
				}

				$userConfig	= Config::get($user['universe']);
				
				$LNG			= $langObjects[$user['lang']];
				
				$MailSubject	= sprintf($LNG['spec_mail_inactive_title'], $userConfig->game_name.' - '.$userConfig->uni_name);
				$MailRAW		= $LNG->getTemplate('email_inactive');
				
				$MailContent	= str_replace(['{USERNAME}', '{GAMENAME}', '{LASTDATE}', '{HTTPPATH}'], [$user['username'], $userConfig->game_name.' - '.$userConfig->uni_name, _date($LNG['php_tdformat'], $user['onlinetime'], $user['timezone']), HTTP_PATH], $MailRAW);
						
				Mail::send($user['email'], $user['username'], $MailSubject, $MailContent);

				$sql	= 'UPDATE %%USERS%% SET `inactive_mail` = 1 WHERE `id` = :userId;';
				Database::get()->update($sql, [':userId'	=> $user['id']]);
			}
		}
	}
}