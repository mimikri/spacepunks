<?php
/**
 * Smarty Internal Plugin Compile Capture
 * Compiles the {capture} tag
 *
 * @package    Smarty
 * @subpackage Compiler
 * @author     Uwe Tews
 */

/**
 * Smarty Internal Plugin Compile Capture Class
 *
 * @package    Smarty
 * @subpackage Compiler
 */
class Smarty_Internal_Compile_Capture extends Smarty_Internal_CompileBase
{
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $shorttag_order = ['name'];

    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $optional_attributes = ['name', 'assign', 'append'];

    /**
     * Compiles code for the {$smarty.capture.xxx}
     *
     * @param array                                 $args      array with attributes from parser
     * @param \Smarty_Internal_TemplateCompilerBase $compiler  compiler object
     * @param array                                 $parameter array with compilation parameter
     *
     * @return string compiled code
     */
    public static function compileSpecialVariable(
        $args,
        Smarty_Internal_TemplateCompilerBase $compiler,
        $parameter = null
    ): string {
        return '$_smarty_tpl->smarty->ext->_capture->getBuffer($_smarty_tpl' .
               (isset($parameter[ 1 ]) ? ", {$parameter[ 1 ]})" : ')');
    }

    /**
     * Compiles code for the {capture} tag
     *
     * @param array                                 $args     array with attributes from parser
     * @param \Smarty_Internal_TemplateCompilerBase $compiler compiler object
     * @param null                                  $parameter
     *
     * @return string compiled code
     */
    public function compile($args, Smarty_Internal_TemplateCompilerBase $compiler, $parameter = null): string
    {
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);
        $buffer = $_attr[ 'name' ] ?? "'default'";
        $assign = $_attr[ 'assign' ] ?? 'null';
        $append = $_attr[ 'append' ] ?? 'null';
        $compiler->_cache[ 'capture_stack' ][] = [$compiler->nocache];
        // maybe nocache because of nocache variables
        $compiler->nocache = $compiler->nocache | $compiler->tag_nocache;
        $_output = "<?php \$_smarty_tpl->smarty->ext->_capture->open(\$_smarty_tpl, $buffer, $assign, $append);?>";
        return $_output;
    }
}

/**
 * Smarty Internal Plugin Compile Captureclose Class
 *
 * @package    Smarty
 * @subpackage Compiler
 */
class Smarty_Internal_Compile_CaptureClose extends Smarty_Internal_CompileBase
{
    /**
     * Compiles code for the {/capture} tag
     *
     * @param array                                 $args     array with attributes from parser
     * @param \Smarty_Internal_TemplateCompilerBase $compiler compiler object
     * @param null                                  $parameter
     *
     * @return string compiled code
     */
    public function compile($args, Smarty_Internal_TemplateCompilerBase $compiler, $parameter): string
    {
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);
        // must endblock be nocache?
        if ($compiler->nocache) {
            $compiler->tag_nocache = true;
        }
        [$compiler->nocache] = array_pop($compiler->_cache[ 'capture_stack' ]);
        return "<?php \$_smarty_tpl->smarty->ext->_capture->close(\$_smarty_tpl);?>";
    }
}
