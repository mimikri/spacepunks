<?php

/**
 * Smarty {block} tag class
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */
class Smarty_Internal_Block
{
    /**
     * Hide attribute
     *
     * @var bool
     */
    public $hide = false;

    /**
     * Append attribute
     *
     * @var bool
     */
    public $append = false;

    /**
     * prepend attribute
     *
     * @var bool
     */
    public $prepend = false;

    /**
     * Block calls $smarty.block.child
     *
     * @var bool
     */
    public $callsChild = false;

    /**
     * Inheritance child block
     *
     * @var Smarty_Internal_Block|null
     */
    public $child = null;

    /**
     * Inheritance calling parent block
     *
     * @var Smarty_Internal_Block|null
     */
    public $parent = null;

    /**
     * Smarty_Internal_Block constructor.
     * - if outer level {block} of child template ($state === 1) save it as child root block
     * - otherwise process inheritance and render
     *
     * @param string   $name     block name
     * @param int|null $tplIndex index of outer level {block} if nested
     */
    public function __construct(
        /**
         * Block name
         */
        public $name,
        /**
         * Inheritance Template index
         */
        public $tplIndex
    )
    {
    }

    /**
     * Compiled block code overloaded by {block} class
     *
     * @param \Smarty_Internal_Template $tpl
     */
    public function callBlock(Smarty_Internal_Template $tpl)
    {
    }
}
