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

if ($USER['authlevel'] == AUTH_USR)
{
	throw new PagePermissionException("Permission error!");
}

function ShowDumpPage(): void
{
	global $LNG;
	switch(empty($_REQUEST['action']) ? '' : $_REQUEST['action'])
	{
		case 'dump':
			$dbTables	= HTTP::_GP('dbtables', []);
			if(empty($dbTables)) {
				$template	= new template();
				$template->message($LNG['du_not_tables_selected']);
				exit;
			}
			
			$fileName	= 'spacepunksBackup_'.date('d_m_Y_H_i_s', TIMESTAMP).'.sql';
			$filePath	= 'includes/backups/'.$fileName;
		
			require 'includes/classes/SQLDumper.class.php';
		
			$dump	= new SQLDumper;
			$dump->dumpTablesToFile($dbTables, $filePath);
			
			$template	= new template();
			$template->message(sprintf($LNG['du_success'], 'includes/backups/'.$fileName));
		break;
		default:
			$dumpData['perRequest']		= 100;

			$dumpData		= [];

			$prefixCounts	= strlen((string) DB_PREFIX);

			$dumpData['sqlTables']	= [];
			$sqlTableRaw			= $GLOBALS['DATABASE']->query("SHOW TABLE STATUS FROM `".DB_NAME."`;");

			while($table = $GLOBALS['DATABASE']->fetchArray($sqlTableRaw))
			{
				if(DB_PREFIX == substr((string) $table['Name'], 0, $prefixCounts))
				{
					$dumpData['sqlTables'][]	= $table['Name'];
				}
			}

			$template	= new template();

			$template->assign_vars(['dumpData'	=> $dumpData]);
			
			$template->show('DumpPage.tpl');
		break;
	}
}