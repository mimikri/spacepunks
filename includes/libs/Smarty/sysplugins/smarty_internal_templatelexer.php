<?php
/*
 * This file is part of Smarty.
 *
 * (c) 2015 Uwe Tews
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Smarty_Internal_Templatelexer
 * This is the template file lexer.
 * It is generated from the smarty_internal_templatelexer.plex file
 *
 *
 * @author Uwe Tews <uwe.tews@googlemail.com>
 */
class Smarty_Internal_Templatelexer
{
    /**
     * Source length
     *
     * @var int
     */
    public $dataLength = null;

    /**
     * byte counter
     *
     * @var int
     */
    public $counter;

    /**
     * token number
     *
     * @var int
     */
    public $token;

    /**
     * token value
     *
     * @var string
     */
    public $value;

    /**
     * current line
     *
     * @var int
     */
    public $line;

    /**
     * tag start line
     *
     * @var
     */
    public $taglineno;

    /**
     * php code type
     *
     * @var string
     */
    public $phpType = '';

   /**
     * state number
     *
     * @var int
     */
    public $state = 1;

    /**
     * Smarty object
     *
     * @var Smarty
     */
    public $smarty = null;

    /**
     * compiler object
     *
     * @var Smarty_Internal_TemplateCompilerBase
     */
    public $compiler = null;

    /**
     * trace file
     *
     * @var resource
     */
    public $yyTraceFILE;

    /**
     * trace prompt
     *
     * @var string
     */
    public $yyTracePrompt;

    /**
     * XML flag true while processing xml
     *
     * @var bool
     */
    public $is_xml = false;

    /**
     * state names
     *
     * @var array
     */
    public $state_name = [1 => 'TEXT', 2 => 'TAG', 3 => 'TAGBODY', 4 => 'LITERAL', 5 => 'DOUBLEQUOTEDSTRING'];

    /**
     * token names
     *
     * @var array
     */
    public $smarty_token_names = [
        // Text for parser error messages
        'NOT'         => '(!,not)',
        'OPENP'       => '(',
        'CLOSEP'      => ')',
        'OPENB'       => '[',
        'CLOSEB'      => ']',
        'PTR'         => '->',
        'APTR'        => '=>',
        'EQUAL'       => '=',
        'NUMBER'      => 'number',
        'UNIMATH'     => '+" , "-',
        'MATH'        => '*" , "/" , "%',
        'INCDEC'      => '++" , "--',
        'SPACE'       => ' ',
        'DOLLAR'      => '$',
        'SEMICOLON'   => ';',
        'COLON'       => ':',
        'DOUBLECOLON' => '::',
        'AT'          => '@',
        'HATCH'       => '#',
        'QUOTE'       => '"',
        'BACKTICK'    => '`',
        'VERT'        => '"|" modifier',
        'DOT'         => '.',
        'COMMA'       => '","',
        'QMARK'       => '"?"',
        'ID'          => 'id, name',
        'TEXT'        => 'text',
        'LDELSLASH'   => '{/..} closing tag',
        'LDEL'        => '{...} Smarty tag',
        'COMMENT'     => 'comment',
        'AS'          => 'as',
        'TO'          => 'to',
        'PHP'         => '"<?php", "<%", "{php}" tag',
        'LOGOP'       => '"<", "==" ... logical operator',
        'TLOGOP'      => '"lt", "eq" ... logical operator; "is div by" ... if condition',
        'SCOND'       => '"is even" ... if condition',
    ];

    /**
     * literal tag nesting level
     */
    private int $literal_cnt = 0;

    /**
     * preg token pattern for state TEXT
     *
     * @var string
     */
    private $yy_global_pattern1 = null;

    /**
     * preg token pattern for state TAG
     *
     * @var string
     */
    private $yy_global_pattern2 = null;

    /**
     * preg token pattern for state TAGBODY
     *
     * @var string
     */
    private $yy_global_pattern3 = null;

    /**
     * preg token pattern for state LITERAL
     *
     * @var string
     */
    private $yy_global_pattern4 = null;

    /**
     * preg token pattern for state DOUBLEQUOTEDSTRING
     *
     * @var null
     */
    private $yy_global_pattern5 = null;

    /**
     * preg token pattern for text
     *
     * @var null
     */
    private $yy_global_text = null;

    /**
     * preg token pattern for literal
     *
     * @var null
     */
    private $yy_global_literal = null;

    /**
     * constructor
     *
     * @param string $data template source
     * @param Smarty_Internal_TemplateCompilerBase $compiler
     */
    public function __construct(/**
     * Source
     */
    public $data, Smarty_Internal_TemplateCompilerBase $compiler)
    {
        $this->dataLength = strlen($this->data);
        $this->counter = 0;
        if (preg_match('/^\xEF\xBB\xBF/i', $this->data, $match)) {
            $this->counter += strlen($match[0]);
        }
        $this->line = 1;
        $this->smarty = $compiler->template->smarty;
        $this->compiler = $compiler;
        $this->compiler->initDelimiterPreg();
        $this->smarty_token_names['LDEL'] = $this->smarty->getLeftDelimiter();
        $this->smarty_token_names['RDEL'] = $this->smarty->getRightDelimiter();
    }

    /**
     * open lexer/parser trace file
     *
     */
    public function PrintTrace(): void
    {
        $this->yyTraceFILE = fopen('php://output', 'w');
        $this->yyTracePrompt = '<br>';
    }

   /**
     * replace placeholders with runtime preg  code
     *
     * @param string $preg
     *
     * @return string
     */
   public function replace($preg)
   {
        return $this->compiler->replaceDelimiter($preg);
   }

    /**
     * check if current value is an autoliteral left delimiter
     *
     * @return bool
     */
    public function isAutoLiteral(): bool
    {
        return $this->smarty->getAutoLiteral() && isset($this->value[ $this->compiler->getLdelLength() ]) ?
            str_contains(" \n\t\r", $this->value[ $this->compiler->getLdelLength() ]) : false;
    }

     
    private int $_yy_state = 1;
    private array $_yy_stack = [];

    public function yylex()
    {
        return $this->{'yylex' . $this->_yy_state}();
    }

    public function yypushstate(int $state): void
    {
        if ($this->yyTraceFILE) {
             fprintf($this->yyTraceFILE, "%sState push %s\n", $this->yyTracePrompt, $this->state_name[$this->_yy_state] ?? $this->_yy_state);
        }
        array_push($this->_yy_stack, $this->_yy_state);
        $this->_yy_state = $state;
        if ($this->yyTraceFILE) {
             fprintf($this->yyTraceFILE, "%snew State %s\n", $this->yyTracePrompt, $this->state_name[$this->_yy_state] ?? $this->_yy_state);
        }
    }

    public function yypopstate(): void
    {
       if ($this->yyTraceFILE) {
             fprintf($this->yyTraceFILE, "%sState pop %s\n", $this->yyTracePrompt,  $this->state_name[$this->_yy_state] ?? $this->_yy_state);
        }
       $this->_yy_state = array_pop($this->_yy_stack);
        if ($this->yyTraceFILE) {
             fprintf($this->yyTraceFILE, "%snew State %s\n", $this->yyTracePrompt, $this->state_name[$this->_yy_state] ?? $this->_yy_state);
        }

    }

    public function yybegin(int $state): void
    {
       $this->_yy_state = $state;
        if ($this->yyTraceFILE) {
             fprintf($this->yyTraceFILE, "%sState set %s\n", $this->yyTracePrompt, $this->state_name[$this->_yy_state] ?? $this->_yy_state);
        }
    }


     
    public function yylex1()
    {
        if (!isset($this->yy_global_pattern1)) {
            $this->yy_global_pattern1 = $this->replace("/\G([{][}])|\G((SMARTYldel)SMARTYal[*])|\G((SMARTYldel)SMARTYautoliteral\\s+SMARTYliteral)|\G((SMARTYldel)SMARTYalliteral\\s*SMARTYrdel)|\G((SMARTYldel)SMARTYal[\/]literal\\s*SMARTYrdel)|\G((SMARTYldel)SMARTYal)|\G([\S\s])/isS");
        }
        if (!isset($this->dataLength)) {
            $this->dataLength = strlen($this->data);
        }
        if ($this->counter >=  $this->dataLength) {
            return false; // end of input
        }
        
        do {
            if (preg_match($this->yy_global_pattern1,$this->data, $yymatches, 0, $this->counter)) {
                if (!isset($yymatches[ 0 ][1])) {
                   $yymatches = preg_grep("/(.|\s)+/", $yymatches);
                } else {
                    $yymatches = array_filter($yymatches);
                }
                if (empty($yymatches)) {
                    throw new Exception('Error: lexing failed because a rule matched' .
                        ' an empty string.  Input "' . substr($this->data,
                        $this->counter, 5) . '... state TEXT');
                }
                next($yymatches); // skip global match
                $this->token = key($yymatches); // token number
                $this->value = current($yymatches); // token value
                $r = $this->{'yy_r1_' . $this->token}();
                if ($r === null) {
                    $this->counter += strlen((string) $this->value);
                    $this->line += substr_count((string) $this->value, "\n");
                    // accept this token
                    return true;
                } elseif ($r === true) {
                    // we have changed state
                    // process this token in the new state
                    return $this->yylex();
                } elseif ($r === false) {
                    $this->counter += strlen((string) $this->value);
                    $this->line += substr_count((string) $this->value, "\n");
                    if ($this->counter >=  $this->dataLength) {
                        return false; // end of input
                    }
                    // skip this token
                    continue;
                }            } else {
                throw new Exception('Unexpected input at line' . $this->line .
                    ': ' . $this->data[$this->counter]);
            }
            break;
        } while (true);

    } // end function


    const TEXT = 1;
    public function yy_r1_1(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_TEXT;
         }
    public function yy_r1_2(): bool
    {

       $to = $this->dataLength;
       preg_match("/[*]{$this->compiler->getRdelPreg()}[\n]?/",$this->data,$match,PREG_OFFSET_CAPTURE,$this->counter);
        if (isset($match[0][1])) {
            $to = $match[0][1] + strlen($match[0][0]);
        } else {
            $this->compiler->trigger_template_error ("missing or misspelled comment closing tag '{$this->smarty->getRightDelimiter()}'");
        }
        $this->value = substr($this->data,$this->counter,$to-$this->counter);
        return false;
         }
    public function yy_r1_4(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_TEXT;
         }
    public function yy_r1_6(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_LITERALSTART;
        $this->yypushstate(self::LITERAL);
         }
    public function yy_r1_8(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_LITERALEND;
        $this->yypushstate(self::LITERAL);
         }
    public function yy_r1_10(): bool
    {

        $this->yypushstate(self::TAG);
        return true;
         }
    public function yy_r1_12(): void
    {

       if (!isset($this->yy_global_text)) {
           $this->yy_global_text = $this->replace('/(SMARTYldel)SMARTYal/isS');
       }
       $to = $this->dataLength;
       preg_match($this->yy_global_text, $this->data,$match,PREG_OFFSET_CAPTURE,$this->counter);
       if (isset($match[0][1])) {
         $to = $match[0][1];
       }
       $this->value = substr($this->data,$this->counter,$to-$this->counter);
       $this->token = Smarty_Internal_Templateparser::TP_TEXT;
         }

     
    public function yylex2()
    {
        if (!isset($this->yy_global_pattern2)) {
            $this->yy_global_pattern2 = $this->replace("/\G((SMARTYldel)SMARTYal(if|elseif|else if|while)\\s+)|\G((SMARTYldel)SMARTYalfor\\s+)|\G((SMARTYldel)SMARTYalforeach(?![^\s]))|\G((SMARTYldel)SMARTYalsetfilter\\s+)|\G((SMARTYldel)SMARTYalmake_nocache\\s+)|\G((SMARTYldel)SMARTYal[0-9]*[a-zA-Z_]\\w*(\\s+nocache)?\\s*SMARTYrdel)|\G((SMARTYldel)SMARTYal[$]smarty\\.block\\.(child|parent)\\s*SMARTYrdel)|\G((SMARTYldel)SMARTYal[\/][0-9]*[a-zA-Z_]\\w*\\s*SMARTYrdel)|\G((SMARTYldel)SMARTYal[$][0-9]*[a-zA-Z_]\\w*(\\s+nocache)?\\s*SMARTYrdel)|\G((SMARTYldel)SMARTYal[\/])|\G((SMARTYldel)SMARTYal)/isS");
        }
        if (!isset($this->dataLength)) {
            $this->dataLength = strlen($this->data);
        }
        if ($this->counter >=  $this->dataLength) {
            return false; // end of input
        }
        
        do {
            if (preg_match($this->yy_global_pattern2,$this->data, $yymatches, 0, $this->counter)) {
                if (!isset($yymatches[ 0 ][1])) {
                   $yymatches = preg_grep("/(.|\s)+/", $yymatches);
                } else {
                    $yymatches = array_filter($yymatches);
                }
                if (empty($yymatches)) {
                    throw new Exception('Error: lexing failed because a rule matched' .
                        ' an empty string.  Input "' . substr($this->data,
                        $this->counter, 5) . '... state TAG');
                }
                next($yymatches); // skip global match
                $this->token = key($yymatches); // token number
                $this->value = current($yymatches); // token value
                $r = $this->{'yy_r2_' . $this->token}();
                if ($r === null) {
                    $this->counter += strlen((string) $this->value);
                    $this->line += substr_count((string) $this->value, "\n");
                    // accept this token
                    return true;
                } elseif ($r === true) {
                    // we have changed state
                    // process this token in the new state
                    return $this->yylex();
                } elseif ($r === false) {
                    $this->counter += strlen((string) $this->value);
                    $this->line += substr_count((string) $this->value, "\n");
                    if ($this->counter >=  $this->dataLength) {
                        return false; // end of input
                    }
                    // skip this token
                    continue;
                }            } else {
                throw new Exception('Unexpected input at line' . $this->line .
                    ': ' . $this->data[$this->counter]);
            }
            break;
        } while (true);

    } // end function


    const TAG = 2;
    public function yy_r2_1(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_LDELIF;
        $this->yybegin(self::TAGBODY);
        $this->taglineno = $this->line;
         }
    public function yy_r2_4(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_LDELFOR;
        $this->yybegin(self::TAGBODY);
        $this->taglineno = $this->line;
         }
    public function yy_r2_6(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_LDELFOREACH;
        $this->yybegin(self::TAGBODY);
        $this->taglineno = $this->line;
         }
    public function yy_r2_8(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_LDELSETFILTER;
        $this->yybegin(self::TAGBODY);
        $this->taglineno = $this->line;
         }
    public function yy_r2_10(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_LDELMAKENOCACHE;
        $this->yybegin(self::TAGBODY);
        $this->taglineno = $this->line;
         }
    public function yy_r2_12(): void
    {

        $this->yypopstate();
        $this->token = Smarty_Internal_Templateparser::TP_SIMPLETAG;
        $this->taglineno = $this->line;
         }
    public function yy_r2_15(): void
    {

         $this->yypopstate();
         $this->token = Smarty_Internal_Templateparser::TP_SMARTYBLOCKCHILDPARENT;
         $this->taglineno = $this->line;
         }
    public function yy_r2_18(): void
    {

        $this->yypopstate();
        $this->token = Smarty_Internal_Templateparser::TP_CLOSETAG;
        $this->taglineno = $this->line;
         }
    public function yy_r2_20(): void
    {

        if ($this->_yy_stack[count($this->_yy_stack)-1] === self::TEXT) {
            $this->yypopstate();
            $this->token = Smarty_Internal_Templateparser::TP_SIMPELOUTPUT;
            $this->taglineno = $this->line;
        } else {
            $this->value = $this->smarty->getLeftDelimiter();
            $this->token = Smarty_Internal_Templateparser::TP_LDEL;
            $this->yybegin(self::TAGBODY);
            $this->taglineno = $this->line;
        }
         }
    public function yy_r2_23(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_LDELSLASH;
        $this->yybegin(self::TAGBODY);
        $this->taglineno = $this->line;
         }
    public function yy_r2_25(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_LDEL;
        $this->yybegin(self::TAGBODY);
        $this->taglineno = $this->line;
         }

     
    public function yylex3()
    {
        if (!isset($this->yy_global_pattern3)) {
            $this->yy_global_pattern3 = $this->replace("/\G(\\s*SMARTYrdel)|\G((SMARTYldel)SMARTYal)|\G([\"])|\G('[^'\\\\]*(?:\\\\.[^'\\\\]*)*')|\G([$][0-9]*[a-zA-Z_]\\w*)|\G([$])|\G(\\s+is\\s+in\\s+)|\G(\\s+as\\s+)|\G(\\s+to\\s+)|\G(\\s+step\\s+)|\G(\\s+instanceof\\s+)|\G(\\s*([!=][=]{1,2}|[<][=>]?|[>][=]?|[&|]{2})\\s*)|\G(\\s+(eq|ne|neq|gt|ge|gte|lt|le|lte|mod|and|or|xor)\\s+)|\G(\\s+is\\s+(not\\s+)?(odd|even|div)\\s+by\\s+)|\G(\\s+is\\s+(not\\s+)?(odd|even))|\G([!]\\s*|not\\s+)|\G([(](int(eger)?|bool(ean)?|float|double|real|string|binary|array|object)[)]\\s*)|\G(\\s*[(]\\s*)|\G(\\s*[)])|\G(\\[\\s*)|\G(\\s*\\])|\G(\\s*[-][>]\\s*)|\G(\\s*[=][>]\\s*)|\G(\\s*[=]\\s*)|\G(([+]|[-]){2})|\G(\\s*([+]|[-])\\s*)|\G(\\s*([*]{1,2}|[%\/^&]|[<>]{2})\\s*)|\G([@])|\G(array\\s*[(]\\s*)|\G([#])|\G(\\s+[0-9]*[a-zA-Z_][a-zA-Z0-9_\-:]*\\s*[=]\\s*)|\G(([0-9]*[a-zA-Z_]\\w*)?(\\\\[0-9]*[a-zA-Z_]\\w*)+)|\G([0-9]*[a-zA-Z_]\\w*)|\G(\\d+)|\G([`])|\G([|][@]?)|\G([.])|\G(\\s*[,]\\s*)|\G(\\s*[;]\\s*)|\G([:]{2})|\G(\\s*[:]\\s*)|\G(\\s*[?]\\s*)|\G(0[xX][0-9a-fA-F]+)|\G(\\s+)|\G([\S\s])/isS");
        }
        if (!isset($this->dataLength)) {
            $this->dataLength = strlen($this->data);
        }
        if ($this->counter >=  $this->dataLength) {
            return false; // end of input
        }
        
        do {
            if (preg_match($this->yy_global_pattern3,$this->data, $yymatches, 0, $this->counter)) {
                if (!isset($yymatches[ 0 ][1])) {
                   $yymatches = preg_grep("/(.|\s)+/", $yymatches);
                } else {
                    $yymatches = array_filter($yymatches);
                }
                if (empty($yymatches)) {
                    throw new Exception('Error: lexing failed because a rule matched' .
                        ' an empty string.  Input "' . substr($this->data,
                        $this->counter, 5) . '... state TAGBODY');
                }
                next($yymatches); // skip global match
                $this->token = key($yymatches); // token number
                $this->value = current($yymatches); // token value
                $r = $this->{'yy_r3_' . $this->token}();
                if ($r === null) {
                    $this->counter += strlen((string) $this->value);
                    $this->line += substr_count((string) $this->value, "\n");
                    // accept this token
                    return true;
                } elseif ($r === true) {
                    // we have changed state
                    // process this token in the new state
                    return $this->yylex();
                } elseif ($r === false) {
                    $this->counter += strlen((string) $this->value);
                    $this->line += substr_count((string) $this->value, "\n");
                    if ($this->counter >=  $this->dataLength) {
                        return false; // end of input
                    }
                    // skip this token
                    continue;
                }            } else {
                throw new Exception('Unexpected input at line' . $this->line .
                    ': ' . $this->data[$this->counter]);
            }
            break;
        } while (true);

    } // end function


    const TAGBODY = 3;
    public function yy_r3_1(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_RDEL;
        $this->yypopstate();
         }
    public function yy_r3_2(): bool
    {

        $this->yypushstate(self::TAG);
        return true;
         }
    public function yy_r3_4(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_QUOTE;
        $this->yypushstate(self::DOUBLEQUOTEDSTRING);
        $this->compiler->enterDoubleQuote();
         }
    public function yy_r3_5(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_SINGLEQUOTESTRING;
         }
    public function yy_r3_6(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_DOLLARID;
         }
    public function yy_r3_7(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_DOLLAR;
         }
    public function yy_r3_8(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_ISIN;
         }
    public function yy_r3_9(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_AS;
         }
    public function yy_r3_10(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_TO;
         }
    public function yy_r3_11(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_STEP;
         }
    public function yy_r3_12(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_INSTANCEOF;
         }
    public function yy_r3_13(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_LOGOP;
         }
    public function yy_r3_15(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_SLOGOP;
         }
    public function yy_r3_17(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_TLOGOP;
         }
    public function yy_r3_20(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_SINGLECOND;
         }
    public function yy_r3_23(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_NOT;
         }
    public function yy_r3_24(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_TYPECAST;
         }
    public function yy_r3_28(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_OPENP;
         }
    public function yy_r3_29(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_CLOSEP;
         }
    public function yy_r3_30(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_OPENB;
         }
    public function yy_r3_31(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_CLOSEB;
         }
    public function yy_r3_32(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_PTR;
         }
    public function yy_r3_33(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_APTR;
         }
    public function yy_r3_34(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_EQUAL;
         }
    public function yy_r3_35(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_INCDEC;
         }
    public function yy_r3_37(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_UNIMATH;
         }
    public function yy_r3_39(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_MATH;
         }
    public function yy_r3_41(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_AT;
         }
    public function yy_r3_42(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_ARRAYOPEN;
         }
    public function yy_r3_43(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_HATCH;
         }
    public function yy_r3_44(): void
    {

        // resolve conflicts with shorttag and right_delimiter starting with '='
        if (substr($this->data, $this->counter + strlen($this->value) - 1, $this->compiler->getRdelLength()) === $this->smarty->getRightDelimiter()) {
            preg_match('/\s+/',$this->value,$match);
            $this->value = $match[0];
            $this->token = Smarty_Internal_Templateparser::TP_SPACE;
        } else {
            $this->token = Smarty_Internal_Templateparser::TP_ATTR;
        }
         }
    public function yy_r3_45(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_NAMESPACE;
         }
    public function yy_r3_48(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_ID;
         }
    public function yy_r3_49(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_INTEGER;
         }
    public function yy_r3_50(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_BACKTICK;
        $this->yypopstate();
         }
    public function yy_r3_51(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_VERT;
         }
    public function yy_r3_52(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_DOT;
         }
    public function yy_r3_53(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_COMMA;
         }
    public function yy_r3_54(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_SEMICOLON;
         }
    public function yy_r3_55(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_DOUBLECOLON;
         }
    public function yy_r3_56(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_COLON;
         }
    public function yy_r3_57(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_QMARK;
         }
    public function yy_r3_58(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_HEX;
         }
    public function yy_r3_59(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_SPACE;
         }
    public function yy_r3_60(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_TEXT;
         }


     
    public function yylex4()
    {
        if (!isset($this->yy_global_pattern4)) {
            $this->yy_global_pattern4 = $this->replace("/\G((SMARTYldel)SMARTYalliteral\\s*SMARTYrdel)|\G((SMARTYldel)SMARTYal[\/]literal\\s*SMARTYrdel)|\G([\S\s])/isS");
        }
        if (!isset($this->dataLength)) {
            $this->dataLength = strlen($this->data);
        }
        if ($this->counter >=  $this->dataLength) {
            return false; // end of input
        }
        
        do {
            if (preg_match($this->yy_global_pattern4,$this->data, $yymatches, 0, $this->counter)) {
                if (!isset($yymatches[ 0 ][1])) {
                   $yymatches = preg_grep("/(.|\s)+/", $yymatches);
                } else {
                    $yymatches = array_filter($yymatches);
                }
                if (empty($yymatches)) {
                    throw new Exception('Error: lexing failed because a rule matched' .
                        ' an empty string.  Input "' . substr($this->data,
                        $this->counter, 5) . '... state LITERAL');
                }
                next($yymatches); // skip global match
                $this->token = key($yymatches); // token number
                $this->value = current($yymatches); // token value
                $r = $this->{'yy_r4_' . $this->token}();
                if ($r === null) {
                    $this->counter += strlen((string) $this->value);
                    $this->line += substr_count((string) $this->value, "\n");
                    // accept this token
                    return true;
                } elseif ($r === true) {
                    // we have changed state
                    // process this token in the new state
                    return $this->yylex();
                } elseif ($r === false) {
                    $this->counter += strlen((string) $this->value);
                    $this->line += substr_count((string) $this->value, "\n");
                    if ($this->counter >=  $this->dataLength) {
                        return false; // end of input
                    }
                    // skip this token
                    continue;
                }            } else {
                throw new Exception('Unexpected input at line' . $this->line .
                    ': ' . $this->data[$this->counter]);
            }
            break;
        } while (true);

    } // end function


    const LITERAL = 4;
    public function yy_r4_1(): void
    {

        $this->literal_cnt++;
        $this->token = Smarty_Internal_Templateparser::TP_LITERAL;
         }
    public function yy_r4_3(): void
    {

        if ($this->literal_cnt) {
             $this->literal_cnt--;
            $this->token = Smarty_Internal_Templateparser::TP_LITERAL;
        } else {
            $this->token = Smarty_Internal_Templateparser::TP_LITERALEND;
            $this->yypopstate();
        }
         }
    public function yy_r4_5(): void
    {

       if (!isset($this->yy_global_literal)) {
           $this->yy_global_literal = $this->replace('/(SMARTYldel)SMARTYal[\/]?literalSMARTYrdel/isS');
       }
       $to = $this->dataLength;
       preg_match($this->yy_global_literal, $this->data,$match,PREG_OFFSET_CAPTURE,$this->counter);
       if (isset($match[0][1])) {
         $to = $match[0][1];
       } else {
          $this->compiler->trigger_template_error ("missing or misspelled literal closing tag");
       }
       $this->value = substr($this->data,$this->counter,$to-$this->counter);
       $this->token = Smarty_Internal_Templateparser::TP_LITERAL;
         }

     
    public function yylex5()
    {
        if (!isset($this->yy_global_pattern5)) {
            $this->yy_global_pattern5 = $this->replace("/\G((SMARTYldel)SMARTYautoliteral\\s+SMARTYliteral)|\G((SMARTYldel)SMARTYalliteral\\s*SMARTYrdel)|\G((SMARTYldel)SMARTYal[\/]literal\\s*SMARTYrdel)|\G((SMARTYldel)SMARTYal[\/])|\G((SMARTYldel)SMARTYal[0-9]*[a-zA-Z_]\\w*)|\G((SMARTYldel)SMARTYal)|\G([\"])|\G([`][$])|\G([$][0-9]*[a-zA-Z_]\\w*)|\G([$])|\G(([^\"\\\\]*?)((?:\\\\.[^\"\\\\]*?)*?)(?=((SMARTYldel)SMARTYal|\\$|`\\$|\"SMARTYliteral)))|\G([\S\s])/isS");
        }
        if (!isset($this->dataLength)) {
            $this->dataLength = strlen($this->data);
        }
        if ($this->counter >=  $this->dataLength) {
            return false; // end of input
        }
        
        do {
            if (preg_match($this->yy_global_pattern5,$this->data, $yymatches, 0, $this->counter)) {
                if (!isset($yymatches[ 0 ][1])) {
                   $yymatches = preg_grep("/(.|\s)+/", $yymatches);
                } else {
                    $yymatches = array_filter($yymatches);
                }
                if (empty($yymatches)) {
                    throw new Exception('Error: lexing failed because a rule matched' .
                        ' an empty string.  Input "' . substr($this->data,
                        $this->counter, 5) . '... state DOUBLEQUOTEDSTRING');
                }
                next($yymatches); // skip global match
                $this->token = key($yymatches); // token number
                $this->value = current($yymatches); // token value
                $r = $this->{'yy_r5_' . $this->token}();
                if ($r === null) {
                    $this->counter += strlen((string) $this->value);
                    $this->line += substr_count((string) $this->value, "\n");
                    // accept this token
                    return true;
                } elseif ($r === true) {
                    // we have changed state
                    // process this token in the new state
                    return $this->yylex();
                } elseif ($r === false) {
                    $this->counter += strlen((string) $this->value);
                    $this->line += substr_count((string) $this->value, "\n");
                    if ($this->counter >=  $this->dataLength) {
                        return false; // end of input
                    }
                    // skip this token
                    continue;
                }            } else {
                throw new Exception('Unexpected input at line' . $this->line .
                    ': ' . $this->data[$this->counter]);
            }
            break;
        } while (true);

    } // end function


    const DOUBLEQUOTEDSTRING = 5;
    public function yy_r5_1(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_TEXT;
         }
    public function yy_r5_3(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_TEXT;
         }
    public function yy_r5_5(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_TEXT;
         }
    public function yy_r5_7(): bool
    {

        $this->yypushstate(self::TAG);
        return true;
         }
    public function yy_r5_9(): bool
    {

        $this->yypushstate(self::TAG);
        return true;
         }
    public function yy_r5_11(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_LDEL;
        $this->taglineno = $this->line;
        $this->yypushstate(self::TAGBODY);
         }
    public function yy_r5_13(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_QUOTE;
        $this->yypopstate();
         }
    public function yy_r5_14(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_BACKTICK;
        $this->value = substr($this->value,0,-1);
        $this->yypushstate(self::TAGBODY);
        $this->taglineno = $this->line;
         }
    public function yy_r5_15(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_DOLLARID;
         }
    public function yy_r5_16(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_TEXT;
         }
    public function yy_r5_17(): void
    {

        $this->token = Smarty_Internal_Templateparser::TP_TEXT;
         }
    public function yy_r5_22(): void
    {

        $to = $this->dataLength;
        $this->value = substr($this->data,$this->counter,$to-$this->counter);
        $this->token = Smarty_Internal_Templateparser::TP_TEXT;
         }

  }

     