<?php

/**
 * Smarty Internal Undefined
 *
 * Class to handle undefined method calls or calls to obsolete runtime extensions
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */
class Smarty_Internal_Undefined
{
    /**
     * Smarty_Internal_Undefined constructor.
     *
     * @param null|string $class name of undefined extension class
     */
    public function __construct(
        /**
         * Name of undefined extension class
         */
        public $class = null
    )
    {
    }

    /**
     * Wrapper for obsolete class Smarty_Internal_Runtime_ValidateCompiled
     *
     * @param \Smarty_Internal_Template $tpl
     * @param array                     $properties special template properties
     * @param bool                      $cache      flag if called from cache file
     *
     * @return bool false
     */
    public function decodeProperties(Smarty_Internal_Template $tpl, $properties, $cache = false): bool
    {
        if ($cache) {
            $tpl->cached->valid = false;
        } else {
            $tpl->mustCompile = true;
        }
        return false;
    }

    /**
     * Call error handler for undefined method
     *
     * @param string $name unknown method-name
     * @param array  $args argument array
     *
     * @return mixed
     * @throws SmartyException
     */
    public function __call($name, array $args)
    {
        if (isset($this->class)) {
            throw new SmartyException("undefined extension class '{$this->class}'");
        } else {
            throw new SmartyException($args[ 0 ]::class . "->{$name}() undefined method");
        }
    }
}
