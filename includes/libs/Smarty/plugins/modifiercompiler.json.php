<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage PluginsModifierCompiler
 */

/**
 * spacepunks json modifier plugin
 *
 * Type:     modifier<br>
 * Name:     json<br>
 * Purpose:  convert variable to json object
 *
 * @author Jan Kröpke
 * @param array $params parameters
 * @return string with compiled code
 */

function smarty_modifiercompiler_json(array $params, $compiler): string
{
	return 'json_encode(' . $params[0] . ')';
}

?>