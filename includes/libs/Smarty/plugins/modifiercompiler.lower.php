<?php
/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage PluginsModifierCompiler
 */
/**
 * Smarty lower modifier plugin
 * Type:     modifier
 * Name:     lower
 * Purpose:  convert string to lowercase
 *
 * @link   https://www.smarty.net/manual/en/language.modifier.lower.php lower (Smarty online manual)
 * @author Monte Ohrt <monte at ohrt dot com>
 * @author Uwe Tews
 *
 * @param array $params parameters
 *
 * @return string with compiled code
 */
function smarty_modifiercompiler_lower(array $params): string
{
    if (Smarty::$_MBSTRING) {
        return 'mb_strtolower(' . $params[ 0 ] . ', \'' . addslashes((string) Smarty::$_CHARSET) . '\')';
    }
    // no MBString fallback
    return 'strtolower(' . $params[ 0 ] . ')';
}
