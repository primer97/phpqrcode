<?php
/*
 * PHP QR Code encoder
 *
 * Config file, feel free to modify
 */

namespace primer\phpqrcode;

class QRConstants
{
    public const QR_CACHEABLE = false; // cache activated
    public const QR_CACHE_DIR = ''; // dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
    
    public const QR_FIND_BEST_MASK   = true; // if true, estimates best mask (spec. default, but extremally slow; set to false to significant performance boost but (propably) worst quality code
    public const QR_FIND_FROM_RANDOM = false; // if false, checks all masks available, otherwise value tells count of masks need to be checked, mask id are got randomly
    public const QR_DEFAULT_MASK     = 2; // when QR_FIND_BEST_MASK === false
    
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
    
    // Supported output formats
    public const QR_FORMAT_TEXT = 0;
    public const QR_FORMAT_PNG  = 1;
    
    public const QR_IMAGE = true;
    
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