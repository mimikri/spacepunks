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

class ShowVertifyPage extends AbstractLoginPage
{
	public static $requireModule = 0;

	function __construct()
	{
		parent::__construct();
	}

	private function _activeUser(): array
	{
		global $LNG;

		$validationID	= HTTP::_GP('i', 0);
		$validationKey	= HTTP::_GP('k', '');

		$db = Database::get();

		$sql = "SELECT * FROM %%USERS_VALID%%
		WHERE validationID	= :validationID
		AND validationKey	= :validationKey
		AND universe		= :universe;";

		$userData = $db->selectSingle($sql, [':validationKey'	=> $validationKey, ':validationID'		=> $validationID, ':universe'			=> Universe::current()]);

		if(empty($userData))
		{
			$this->printMessage($LNG['vertifyNoUserFound']);
		}

		$config	= Config::get();

		$sql = "DELETE FROM %%USERS_VALID%% WHERE validationID = :validationID;";
		$db->delete($sql, [':validationID'	=> $validationID]);

		[$userID, $planetID] = PlayerUtil::createPlayer($userData['universe'], $userData['userName'], $userData['password'], $userData['email'], $userData['language']);

		if($config->mail_active == 1)
		{
			require('includes/classes/Mail.class.php');
			$MailSubject	= sprintf($LNG['registerMailCompleteTitle'], $config->game_name, Universe::current());
			$MailRAW		= $LNG->getTemplate('email_reg_done');
			$MailContent	= str_replace(['{USERNAME}', '{GAMENAME}', '{GAMEMAIL}'], [$userData['userName'], $config->game_name.' - '.$config->uni_name, $config->smtp_sendmail], $MailRAW);

			try {
				Mail::send($userData['email'], $userData['userName'], $MailSubject, $MailContent);
			}
			catch (Exception)
			{
				// This mail is wayne.
			}
		}

		if(!empty($userData['referralID']))
		{
			$sql = "UPDATE %%USERS%% SET
			`ref_id`	= :referralId,
			`ref_bonus`	= 1
			WHERE
			`id`		= :userID;";

			$db->update($sql, [':referralId'	=> $userData['referralID'], ':userID'		=> $userID]);
		}

		if(!empty($userData['externalAuthUID']))
		{
			$sql ="INSERT INTO %%USERS_AUTH%% SET
			`id`		= :userID,
			`account`	= :externalAuthUID,
			`mode`		= :externalAuthMethod;";
			$db->insert($sql, [':userID'				=> $userID, ':externalAuthUID'		=> $userData['externalAuthUID'], ':externalAuthMethod'	=> $userData['externalAuthMethod']]);
		}

		$senderName = $LNG['registerWelcomePMSenderName'];
		$subject 	= $LNG['registerWelcomePMSubject'];
		$message 	= sprintf($LNG['registerWelcomePMText'], $config->game_name, $userData['universe']);

		PlayerUtil::sendMessage($userID, 1, $senderName, 1, $subject, $message, TIMESTAMP);
		
		return ['userID'	=> $userID, 'userName'	=> $userData['userName'], 'planetID'	=> $planetID];
	}

	function show(): void
	{
		$userData	= $this->_activeUser();

		$session	= Session::create();
		$session->userId		= (int) $userData['userID'];
		$session->adminAccess	= 0;
		$session->save();

		HTTP::redirectTo('game.php');
	}

	function json(): void
	{
		global $LNG;
		$userData	= $this->_activeUser();
		$this->sendJSON(sprintf($LNG['vertifyAdminMessage'], $userData['userName']));
	}
}