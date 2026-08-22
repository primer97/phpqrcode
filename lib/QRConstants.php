<?php
/*
 * PHP QR Code encoder
 *
 * Config file, feel free to modify
 */

namespace primer\phpqrcode;

class QRConstants
{
    public const QR_PNG_MAXIMUM_SIZE = 1024; // maximum allowed png image width (in pixels), tune to make sure GD and PHP can handle such big images
    
    // Encoding modes
    public const QR_MODE_NUL       = -1;
    public const QR_MODE_NUM       = 0;
    public const QR_MODE_AN        = 1;
    public const QR_MODE_8         = 2;
    public const QR_MODE_KANJI     = 3;
    public const QR_MODE_STRUCTURE = 4;
    
    // Levels of error correction.
    public const QR_ECLEVEL_L = 0;
    public const QR_ECLEVEL_M = 1;
    public const QR_ECLEVEL_Q = 2;
    public const QR_ECLEVEL_H = 3;
    
    public const STRUCTURE_HEADER_BITS  = 20;
    public const MAX_STRUCTURED_SYMBOLS = 16;
    
    // Maks
    public const N1 = 3;
    public const N2 = 3;
    public const N3 = 40;
    public const N4 = 10;
    
    public const QRSPEC_VERSION_MAX = 40;
    public const QRSPEC_WIDTH_MAX   = 177;
    
    public const QRCAP_WIDTH    = 0;
    public const QRCAP_WORDS    = 1;
    public const QRCAP_REMINDER = 2;
    public const QRCAP_EC       = 3;
}