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

function ShowChatConfigPage(): void
{
	global $LNG;

	$config = Config::get(Universe::getEmulated());

	if (!empty($_POST))
	{
		$config_before = ['chat_closed'			=> $config->chat_closed, 'chat_allowchan'		=> $config->chat_allowchan, 'chat_allowmes'			=> $config->chat_allowmes, 'chat_allowdelmes'		=> $config->chat_allowdelmes, 'chat_logmessage'		=> $config->chat_logmessage, 'chat_nickchange'		=> $config->chat_nickchange, 'chat_botname'			=> $config->chat_botname, 'chat_channelname'		=> $config->chat_channelname];
		
		$chat_allowchan			= isset($_POST['chat_allowchan']) && $_POST['chat_allowchan'] == 'on' ? 1 : 0;
		$chat_allowmes			= isset($_POST['chat_allowmes']) && $_POST['chat_allowmes'] == 'on' ? 1 : 0;
		$chat_allowdelmes		= isset($_POST['chat_allowdelmes']) && $_POST['chat_allowdelmes'] == 'on' ? 1 : 0;
		$chat_logmessage		= isset($_POST['chat_logmessage']) && $_POST['chat_logmessage'] == 'on' ? 1 : 0;
		$chat_nickchange		= isset($_POST['chat_nickchange']) && $_POST['chat_nickchange'] == 'on' ? 1 : 0;
		$chat_closed			= isset($_POST['chat_closed']) && $_POST['chat_closed'] == 'on' ? 1 : 0;
		
		$chat_channelname		= HTTP::_GP('chat_channelname', '', true);
		$chat_botname			= HTTP::_GP('chat_botname', '', true);
		
		$config_after = ['chat_closed'			=> $chat_closed, 'chat_allowchan'		=> $chat_allowchan, 'chat_allowmes'			=> $chat_allowmes, 'chat_allowdelmes'		=> $chat_allowdelmes, 'chat_logmessage'		=> $chat_logmessage, 'chat_nickchange'		=> $chat_nickchange, 'chat_botname'			=> $chat_botname, 'chat_channelname'		=> $chat_channelname];

		foreach($config_after as $key => $value)
		{
			$config->$key	= $value;
		}
		$config->save();
		
		$LOG = new Log(3);
		$LOG->target = 3;
		$LOG->old = $config_before;
		$LOG->new = $config_after;
		$LOG->save();
	}

	$template	= new template();

	$template->assign_vars(['chat_closed'			=> $config->chat_closed, 'chat_allowchan'		=> $config->chat_allowchan, 'chat_allowmes'			=> $config->chat_allowmes, 'chat_logmessage'		=> $config->chat_logmessage, 'chat_nickchange'		=> $config->chat_nickchange, 'chat_botname'			=> $config->chat_botname, 'chat_channelname'		=> $config->chat_channelname, 'se_server_parameters'	=> $LNG['se_server_parameters'], 'se_save_parameters'	=> $LNG['se_save_parameters'], 'ch_closed'				=> $LNG['ch_closed'], 'ch_allowchan'			=> $LNG['ch_allowchan'], 'ch_allowmes'			=> $LNG['ch_allowmes'], 'ch_allowdelmes'		=> $LNG['ch_allowdelmes'], 'ch_logmessage'			=> $LNG['ch_logmessage'], 'ch_nickchange'			=> $LNG['ch_nickchange'], 'ch_botname'			=> $LNG['ch_botname'], 'ch_channelname'		=> $LNG['ch_channelname']]);
	
	$template->show('ChatConfigBody.tpl');
}
