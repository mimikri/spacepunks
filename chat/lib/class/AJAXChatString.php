<?php
/*
 * @package AJAX_Chat
 * @author Sebastian Tschan
 * @copyright (c) Sebastian Tschan
 * @license Modified MIT License
 * @link https://blueimp.net/ajax/
 */

// Class to provide multibyte enabled string methods
class AJAXChatString {

	public static function subString($str, $start=0, $length=null, $encoding='UTF-8'): string|false {
		if($length === null) {
			$length = AJAXChatString::stringLength($str);
		}		
		if(function_exists('mb_substr')) {
			return mb_substr((string) $str, $start, $length, $encoding);
		} else if(function_exists('iconv_substr')) {
			return iconv_substr((string) $str, $start, $length, $encoding);
		} else {
			return substr((string) $str, $start, $length);
		}
	}
	
	public static function stringLength($str, $encoding='UTF-8'): int|false {
		if(function_exists('mb_strlen')) {
			return mb_strlen((string) $str, $encoding);
		} else if(function_exists('iconv_strlen')) {
			return iconv_strlen((string) $str, $encoding);
		} else {
			return strlen((string) $str);
		}
	}

}
?>