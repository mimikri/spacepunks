<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage PluginsModifierCompiler
 */

/**
 * spacepunks time modifier plugin
 *
 * Type:     modifier<br>
 * Name:     time<br>
 * Purpose:  convert string to lowercase
 *
 * @author Jan Kröpke
 * @param array $params parameters
 * @return string with compiled code
 */

function smarty_modifiercompiler_time(array $params, $compiler): string
{
	return 'pretty_time(' . $params[0] . ')';
}

?>