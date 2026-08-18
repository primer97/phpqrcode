<?php

namespace primer\phpqrcode\Internal;

/**
 * @internal
 */
class QRstr
{
    /**
     * @param array<string>  $srctab
     * @param int    $x
     * @param int    $y
     * @param string $repl
     * @param bool|int $replLen
     * @return void
     */
    public static function set(array &$srctab, int $x, int $y, string $repl, $replLen = false):void
    {
        $srctab[$y] = substr_replace($srctab[$y], ($replLen !== false) ? substr($repl, 0, $replLen) : $repl, $x, ($replLen !== false) ? $replLen : strlen($repl));
    }
}