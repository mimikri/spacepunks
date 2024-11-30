<?php

/**
 * Smarty exception class
 *
 * @package Smarty
 */
class SmartyException extends Exception
{
    public static $escape = false;

    /**
     * @return string
     */
    public function __toString(): string
    {
        return ' --> Smarty: ' . (self::$escape ? htmlentities((string) $this->message) : $this->message) . ' <-- ';
    }
}
