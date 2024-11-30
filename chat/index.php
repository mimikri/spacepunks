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


// Suppress errors:
error_reporting(E_ALL);

// Path to the chat directory:
define('AJAX_CHAT_PATH', dirname((string) $_SERVER['SCRIPT_FILENAME']).'/');

// Include custom libraries and initialization code:
require(AJAX_CHAT_PATH.'lib/custom.php');

// Include Class libraries:
require(AJAX_CHAT_PATH.'lib/classes.php');

// Initialize the chat:
$ajaxChat = new CustomAJAXChat();
