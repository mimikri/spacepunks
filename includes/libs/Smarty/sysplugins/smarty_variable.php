<?php

/**
 * class for the Smarty variable object
 * This class defines the Smarty variable object
 *
 * @package    Smarty
 * @subpackage Template
 */
#[\AllowDynamicProperties]
class Smarty_Variable implements \Stringable
{
    /**
     * create Smarty variable object
     *
     * @param mixed   $value   the value to assign
     * @param boolean $nocache if true any output of this variable will be not cached
     */
    public function __construct(
        /**
         * template variable
         */
        public mixed $value = null,
        /**
         * if true any output of this variable will be not cached
         */
        public $nocache = false
    )
    {
    }

    /**
     * <<magic>> String conversion
     *
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->value;
    }
}
