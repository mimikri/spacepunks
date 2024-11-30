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
 
class SupportTickets
{
	public function createTicket($ownerID, $categoryID, $subject)
	{
		$sql 	= 'INSERT INTO %%TICKETS%% SET
		ownerID		= :ownerId,
		universe	= :universe,
		categoryID	= :categoryId,
		subject		= :subject,
		time		= :time;';

		Database::get()->insert($sql, [':ownerId'		=> $ownerID, ':universe'		=> Universe::current(), ':categoryId'	=> $categoryID, ':subject'		=> $subject, ':time'			=> TIMESTAMP]);
		
		return Database::get()->lastInsertId();
	}

	public function createAnswer($ticketID, $ownerID, $ownerName, $subject, $message, $status)
	{
		$sql = 'INSERT INTO %%TICKETS_ANSWER%% SET
		ticketID	= :ticketId,
		ownerID		= :ownerId,
		ownerName	= :ownerName,
		subject		= :subject,
		message		= :message,
		time		= :time;';

		Database::get()->insert($sql, [':ticketId'		=> $ticketID, ':ownerId'		=> $ownerID, ':ownerName'	=> $ownerName, ':subject'		=> $subject, ':message'		=> $message, ':time'			=> TIMESTAMP]);

		$answerId = Database::get()->lastInsertId();

		$sql	= 'UPDATE %%TICKETS%% SET status = :status WHERE ticketID = :ticketId;';

		Database::get()->update($sql, [':status'	=> $status, ':ticketId'	=> $ticketID]);
		
		return $answerId;
	}

	/**
  * @return mixed[]
  */
 public function getCategoryList(): array
	{
		$sql	= 'SELECT * FROM %%TICKETS_CATEGORY%%;';

		$categoryResult		= Database::get()->select($sql);
		$categoryList		= [];

		foreach($categoryResult as $categoryRow)
		{
			$categoryList[$categoryRow['categoryID']]	= $categoryRow['name'];
		}
		
		return $categoryList;
	}
}
