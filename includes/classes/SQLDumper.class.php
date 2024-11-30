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

class SQLDumper
{	
	public function dumpTablesToFile($dbTables, $filePath): string|bool
	{
		if($this->canNative('mysqldump'))
		{
			return $this->nativeDumpToFile($dbTables, $filePath);
		}
		else
		{
			return $this->softwareDumpToFile($dbTables, $filePath);
		}
	}
	
	private function setTimelimit(): void
	{
		@set_time_limit(600); // 10 Minutes
	}
		
	private function canNative(string $command): bool
	{
		return function_exists('shell_exec') && function_exists('escapeshellarg') && shell_exec("which " . $command) !== "";
	}
	
	private function nativeDumpToFile($dbTables, string $filePath): string
	{
		$database	= [];
		require 'includes/config.php';

        $dbVersion	= Database::get()->selectSingle('SELECT @@version', [], '@@version');
        if(version_compare($dbVersion, '5.5') >= 0) {
            putenv('MYSQL_PWD='.$database['userpw']);
            $passwordArgument = '';
        } else {
            $passwordArgument = "--password='".escapeshellarg($database['userpw'])."'";
        }

		$dbTables	= array_map('escapeshellarg', $dbTables);
		$sqlDump	= shell_exec("mysqldump --host=".escapeshellarg($database['host'])." --port=".((int) $database['port'])." --user=".escapeshellarg($database['user'])." ".$passwordArgument." --no-create-db --order-by-primary --add-drop-table --comments --complete-insert --hex-blob ".escapeshellarg($database['databasename'])." ".implode(' ', $dbTables)." 2>&1 1> ".$filePath);
		if(strlen($sqlDump) !== 0) #mysqldump error
		{
			throw new Exception($sqlDump);
		}
		return $sqlDump;
	}
	
	private function softwareDumpToFile($dbTables, $filePath): bool
	{
		$this->setTimelimit();

		$db	= Database::get();
		$database	= [];
		require 'includes/config.php';
		$integerTypes	= ['tinyint', 'smallint', 'mediumint', 'int', 'bigint', 'decimal', 'float', 'double', 'real'];
		$gameVersion	= Config::get()->VERSION;
		$fp	= fopen($filePath, 'w');
		fwrite($fp, "-- MySQL dump | spacepunks dumper v{$gameVersion}
--
-- Host: {$database['host']}    Database: {$database['databasename']}
-- ------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

");

		foreach($dbTables as $dbTable)
		{
			$numColumns	= [];
			$firstRow	= true;

			fwrite($fp, "--\n-- Table structure for table `{$dbTable}`\n--\n\nDROP TABLE IF EXISTS `{$dbTable}`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;\n\n");

			$createTable	= $db->nativeQuery("SHOW CREATE TABLE ".$dbTable);
            $createTableSql = $createTable['Create Table'] ?? $createTable[0]['Create Table'] ?? false;

            if($createTableSql === false) {
                throw new Exception("Error after executing SHOW CREATE TABLE ".$dbTable."! Can't find key 'Create Table' in the results. Available data: \n\n".print_r($createTable, true));
            }

			fwrite($fp, $createTableSql.';');
			fwrite($fp, "\n\n/*!40101 SET character_set_client = @saved_cs_client */;");

			$sql = "SELECT COUNT(*) as state FROM ".$dbTable.";";

			$count	= $db->nativeQuery($sql);
			if($count[0]['state'] == 0)
			{
				fwrite($fp, "\n\n--\n-- No data for table `{$dbTable}`\n--\n\n");
				continue;
			}

			fwrite($fp, "
			
--
-- Dumping data for table `{$dbTable}`
--

LOCK TABLES `{$dbTable}` WRITE;
/*!40000 ALTER TABLE `{$dbTable}` DISABLE KEYS */;

");
			$columnsData	= $db->nativeQuery("SHOW COLUMNS FROM `".$dbTable."`");
			$columnNames	= [];
			foreach($columnsData as $columnData)
			{
				$columnNames[]	= $columnData['Field'];
				foreach($integerTypes as $type)
				{
					if(str_contains((string) $columnData['Type'], $type.'('))
					{
						$numColumns[]	= $columnData['Field'];
						break;
					}
				}
			}
			
			$insertInto	= "INSERT INTO `{$dbTable}` (`".implode("`, `", $columnNames)."`) VALUES\r\n";
			
			fwrite($fp, $insertInto);
			$i = 0;
			$tableData	= $db->select("SELECT * FROM ".$dbTable);
			foreach($tableData as $tableRow)
			{
				$rowData = [];
				$i++;
				if(($i % 50) === 0)
				{
					$firstRow	= true;
					fwrite($fp, ";\r\n");
					fwrite($fp, $insertInto);
				}
				
				if(!$firstRow)
				{
					fwrite($fp, ",\r\n");
				}
				else
				{
					$firstRow = false;
				}
				
				foreach($tableRow as $colum => $value)
				{
					if(in_array($colum, $numColumns))
					{
						$rowData[]	= $value ?? 'NULL';
					}
					else
					{
						$rowData[]	= $value === NULL ? 'NULL' : $db->quote($value);
					}
				}
				fwrite($fp, "(".implode(", ",$rowData).")");
			}
			fwrite($fp, ";
			
/*!40000 ALTER TABLE `{$dbTable}` ENABLE KEYS */;
UNLOCK TABLES;

");
		}
		fwrite($fp, "/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on ".date("Y-d-m H:i:s"));
		fclose($fp);

		return filesize($filePath) !== 0;
	}
	
	public function restoreDatabase($filePath): void
	{
		// Ugly.
		$this->setTimelimit();
		
		if($this->canNative('mysql'))
		{
			$database	= [];
			require 'includes/config.php';
			$sqlDump	= shell_exec("mysql --host='".escapeshellarg($database['host'])."' --port=".((int) $database['port'])." --user='".escapeshellarg($database['user'])."' --password='".escapeshellarg($database['userpw'])."' '".escapeshellarg($database['databasename'])."' < ".escapeshellarg((string) $filePath)." 2>&1 1> /dev/null");
			if(strlen($sqlDump) !== 0) #mysql error
			{
				throw new Exception($sqlDump);
			}
		}
		else
		{
			$backupQuery	= explode(";\r\n", file_get_contents($filePath));
			foreach($backupQuery as $query)
			{
				Database::get()->nativeQuery($query);
			}
		}
	}
}