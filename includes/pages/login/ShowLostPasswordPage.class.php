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


class ShowLostPasswordPage extends AbstractLoginPage
{
	public static $requireModule = 0;

	function __construct() 
	{
		parent::__construct();
	}
	
	function show(): void 
	{
		$universeSelect	= $this->getUniverseSelector();
		
		$this->assign(['universeSelect'	=> $universeSelect]);
		
		$this->display('page.lostPassword.default.tpl');
	}
	
	function newPassword(): void 
	{
		global $LNG;
		$userID			= HTTP::_GP('u', 0);
		$validationKey	= HTTP::_GP('k', '');

		$db = Database::get();

		$sql = "SELECT COUNT(*) as state FROM %%LOSTPASSWORD%% WHERE userID = :userID AND `key` = :validationKey AND `time` > :time AND hasChanged = 0;";
		$isValid = $db->selectSingle($sql, [':userID'			=> $userID, ':validationKey'	=> $validationKey, ':time'				=> (TIMESTAMP - 1800)], 'state');

		if(empty($isValid))
		{
			$this->printMessage($LNG['passwordValidInValid'], [['label'	=> $LNG['passwordBack'], 'url'	=> 'index.php']]);
		}
		
		$newPassword	= uniqid();

		$sql = "SELECT username, email_2 as mail, universe FROM %%USERS%% WHERE id = :userID;";
		$userData = $db->selectSingle($sql, [':userID'	=> $userID]);

		$config			= Config::get($userData['universe']);

		$MailRAW		= $LNG->getTemplate('email_lost_password_changed');
		$MailContent	= str_replace(['{USERNAME}', '{GAMENAME}', '{GAMEMAIL}', '{PASSWORD}'], [$userData['username'], $config->game_name.' - '.$config->uni_name, $config->smtp_sendmail, $newPassword], $MailRAW);
		
		$sql = "UPDATE %%USERS%% SET password = :newPassword WHERE id = :userID;";
		$db->update($sql, [':userID'		=> $userID, ':newPassword'	=> PlayerUtil::cryptPassword($newPassword)]);

		require 'includes/classes/Mail.class.php';

		$subject	= sprintf($LNG['passwordChangedMailTitle'], $config->game_name);
		Mail::send($userData['mail'], $userData['username'], $subject, $MailContent);

		$sql = "UPDATE %%LOSTPASSWORD%% SET hasChanged = 1 WHERE userID = :userID AND `key` = :validationKey;";
		$db->update($sql, [':userID'			=> $userID, ':validationKey'	=> $validationKey]);

		$this->printMessage($LNG['passwordChangedMailSend'], [['label'	=> $LNG['passwordNext'], 'url'	=> 'index.php']]);
	}
	
	function send(): void
	{
		global $LNG;
		$username	= HTTP::_GP('username', '', UTF8_SUPPORT);
		$mail		= HTTP::_GP('mail', '', true);
		
		$errorMessages	= [];
		
		if(empty($username))
		{
			$errorMessages[]	= $LNG['passwordUsernameEmpty'];
		}
		
		if(empty($mail))
		{
			$errorMessages[]	= $LNG['passwordErrorMailEmpty'];
		}

		$config	= Config::get();

		if ($config->capaktiv == 1)
		{
            require('includes/libs/reCAPTCHA/autoload.php');

            $recaptcha = new \ReCaptcha\ReCaptcha($config->capprivate);
            $resp = $recaptcha->verify(HTTP::_GP('g-recaptcha-response', ''), Session::getClientIp());
            if (!$resp->isSuccess())
            {
                $errorMessages[]	=  $LNG['registerErrorCaptcha'];
			}
		}
		
		if(!empty($errorMessages))
		{
			$message	= implode("<br>\r\n", $errorMessages);
			$this->printMessage($message, [['label'	=> $LNG['passwordBack'], 'url'	=> 'index.php?page=lostPassword']]);
		}
		
		$db = Database::get();

		$sql = "SELECT id FROM %%USERS%% WHERE universe = :universe AND username = :username AND email_2 = :mail;";
		$userID = $db->selectSingle($sql, [':universe'	=> Universe::current(), ':username'	=> $username, ':mail'		=> $mail], 'id');

		if(empty($userID))
		{
			$this->printMessage($LNG['passwordErrorUnknown'], [['label'	=> $LNG['passwordBack'], 'url'	=> 'index.php?page=lostPassword']]);
		}

		$sql = "SELECT COUNT(*) as state FROM %%LOSTPASSWORD%% WHERE userID = :userID AND time > :time AND hasChanged = 0;";
		$hasChanged = $db->selectSingle($sql, [':userID'	=> $userID, ':time'		=> (TIMESTAMP - 86400)], 'state');

		if(!empty($hasChanged))
		{
			$this->printMessage($LNG['passwordErrorOnePerDay'], [['label'	=> $LNG['passwordBack'], 'url'	=> 'index.php?page=lostPassword']]);
		}

		$validationKey	= md5(uniqid());
						
		$MailRAW		= $LNG->getTemplate('email_lost_password_validation');
		
		$MailContent	= str_replace(['{USERNAME}', '{GAMENAME}', '{VALIDURL}'], [$username, $config->game_name.' - '.$config->uni_name, HTTP_PATH.'index.php?page=lostPassword&mode=newPassword&u='.$userID.'&k='.$validationKey], $MailRAW);
		
		require 'includes/classes/Mail.class.php';

		$subject	= sprintf($LNG['passwordValidMailTitle'], $config->game_name);

		Mail::send($mail, $username, $subject, $MailContent);

		$sql = "INSERT INTO %%LOSTPASSWORD%% SET userID = :userID, `key` = :validationKey, `time` = :timestamp, fromIP = :remoteAddr;";
		$db->insert($sql, [':userID'		=> $userID, ':timestamp'	=> TIMESTAMP, ':validationKey'=> $validationKey, ':remoteAddr'	=> Session::getClientIp()]);

		$this->printMessage($LNG['passwordValidMailSend'], [['label'	=> $LNG['passwordNext'], 'url'	=> 'index.php']]);
	}
}