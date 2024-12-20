<?php


/**
 *  spacepunks
 *   by Jan-Otto Kröpke 2009-2016
 *   by mimikri 2023
 *
 * For the full copyright and license information, please view the LICENSE
 *
 * @package spacepunks
 * @author mimikri
 * @copyright 2009 Lucky
 * @copyright 2016 Jan-Otto Kröpke <slaver7@gmail.com>
 * @licence MIT
 *@version 0.0.1
 * @link https://github.com/mimikri/spacepunks
 */

class CustomAJAXChat extends AJAXChat
{
	/**
	 * reference of the database object
	 * @var AJAXChatDataBaseMySQLi
	 */
	public $db;

	function __destruct()
	{
		Session::load()->save();
	}

	function startSession(): void {

	}

	function regenerateSessionID(): void { }

	function destroySession(): void {
		Session::load()->chat = [];
	}

	function getSessionVar($key, $prefix=null) {
		if($prefix === null)
			$prefix = $this->getConfig('sessionKeyPrefix');

		$sessionData	= Session::load()->chat;

		if(isset($sessionData[$prefix.$key]))
			return $sessionData[$prefix.$key];
		else
			return null;
	}

	function setSessionVar($key, $value, $prefix=null): void {
		if($prefix === null)
			$prefix = $this->getConfig('sessionKeyPrefix');

		// Set the session value:
		if(isset(Session::load()->chat))
		{
			$sessionData	= array_merge(Session::load()->chat, [$prefix.$key => $value]);
		}
		else
		{
			$sessionData	= [$prefix.$key => $value];
		}

		Session::load()->__set('chat', $sessionData);
	}

	function initCustomConfig(): void
	{
		define('MODE', 'CHAT');
		define('ROOT_PATH', str_replace('\\', '/',dirname(__FILE__, 4)).'/');
		set_include_path(ROOT_PATH);
		chdir(ROOT_PATH);

		$database		= [];
		require 'includes/config.php';
		require 'includes/common.php';

		$this->setConfig('dbConnection', 'type', 'mysqli');
		$this->setConfig('dbConnection', 'host', $database['host']);
		$this->setConfig('dbConnection', 'user', $database['user']);
		$this->setConfig('dbConnection', 'pass', $database['userpw']);
		$this->setConfig('dbConnection', 'name', $database['databasename']);

		$dbTableNames	= Database::get()->getDbTableNames();
		$dbTableNames	= array_combine($dbTableNames['keys'], $dbTableNames['names']);

		$this->setConfig('dbTableNames', 'online', $dbTableNames['%%CHAT_ON%%']);
		$this->setConfig('dbTableNames', 'messages', $dbTableNames['%%CHAT_MES%%']);
		$this->setConfig('dbTableNames', 'bans', $dbTableNames['%%CHAT_BAN%%']);
		$this->setConfig('dbTableNames', 'invitations', $dbTableNames['%%CHAT_INV%%']);

		$config	= Config::get();

		$this->setConfig('chatBotName', false, $config->chat_botname);
		$this->setConfig('allowUserMessageDelete', false, (bool) $config->chat_allowdelmes);
		$this->setConfig('allowNickChange', false, (bool) $config->chat_nickchange);
		$this->setConfig('chatClosed', false, (bool) $config->chat_closed);
		$this->setConfig('allowPrivateChannels', false, (bool) $config->chat_allowchan);
		$this->setConfig('allowPrivateMessages', false, (bool) $config->chat_allowmes);
		$this->setConfig('defaultChannelName', false, $config->chat_channelname);
		$this->setConfig('showChannelMessages', false, (bool) $config->chat_logmessage);
		$this->setConfig('langAvailable', false, Language::getAllowedLangs());
		$this->setConfig('langNames', false, Language::getAllowedLangs(false));
		$this->setConfig('forceAutoLogin', false, true);
		$this->setConfig('contentType', false, 'text/html');
		$this->setConfig('langDefault', false, DEFAULT_LANG);
		$this->setConfig('allowGuestLogins', false, false);
		$this->setConfig('showChannelMessages', false, false);
		$this->setConfig('styleDefault', false, 'spacepunks');
		$this->setConfig('styleAvailable', false, ['spacepunks']);
	}
	
	function initCustomSession(): void
	{
		if(!$this->getRequestVar('ajax'))
		{
			$this->getAllChannels();
			if(!is_null($this->getChannel()))
			{
				$this->switchChannel($this->getConfig('defaultChannelName'));
			}
		}
	}
	
	function initCustomRequestVars(): void {
		$this->setRequestVar('action', $_REQUEST['action'] ?? '');
	}

	function revalidateUserID(): bool {
		if($this->getUserID() === Session::load()->userId) {
			return true;
		}
		
		return false;
	}
	
	function getValidLoginUserData(): false|array
	{
		$session	= Session::load();
		// Return false if given user is a bot:
		if(!$session->isValidSession())
		{
			return false;
		}

		$sql	= 'SELECT authlevel, username, ally_id FROM %%USERS%% WHERE id = :userId;';

		$sqlData = Database::get()->selectSingle($sql, [':userId'	=> Session::load()->userId]);
		
		$userData = [];
		$userData['userID'] = Session::load()->userId;

		$userData['userName'] = $this->trimUserName($sqlData['username']);
		$userData['userAlly'] = $sqlData['ally_id'];
		
		if($sqlData['authlevel'] == AUTH_ADM)
			$userData['userRole'] = AJAX_CHAT_ADMIN;
		elseif($sqlData['authlevel'] > AUTH_USR)
			$userData['userRole'] = AJAX_CHAT_MODERATOR;
		else
			$userData['userRole'] = AJAX_CHAT_USER;

		return $userData;
	}

	// Store all existing channels
	// Make sure channel names don't contain any whitespace
	function &getAllChannels() {
		if($this->_allChannels === NULL) {
			$this->_allChannels = [$this->trimChannelName($this->getConfig('defaultChannelName')) => $this->getConfig('defaultChannelID')];

			$db		= Database::get();
			$sql	= 'SELECT ally_id FROM %%USERS%% WHERE id = :userId AND id NOT IN (SELECT userid FROM %%ALLIANCE_REQUEST%%);';
			$allianceId = $db->selectSingle($sql, [':userId'	=> Session::load()->userId], 'ally_id');

			$sql	= 'SELECT id, ally_name FROM %%ALLIANCE%% WHERE id = :allianceId';
			$channels = $db->select($sql, [':allianceId'	=> $allianceId]);

			$defaultChannelFound = false;

			foreach($channels as $channel)
			{
				$channel['id'] = $channel['id'] + 100;
				$this->_allChannels[$this->trimChannelName('+'.$channel['ally_name'])] = $channel['id'];
				if(!$defaultChannelFound && $this->getRequestVar('action') == 'alliance' && ($allianceId + 100) == $channel['id'])
				{
					$this->setConfig('defaultChannelName', false, $this->trimChannelName('+'.$channel['ally_name']));
					$this->setConfig('defaultChannelID', false, $channel['id']);
					$defaultChannelFound = true;
				}
			}
		}

		return $this->_allChannels;
	}
}