<?php

namespace primer\phpqrcode;

/**
 * Settings for QRCode generation.
 *
 * * mask settings
 */
class QRSettings
{
    /**
     * Reset to default settings : {@see QRSettings::setFindBestMask() Best Mask}, {@see QRSettings::forceMode() auto-mode}, {@see QRSettings::allowCache() no-cache} mode.
     * @return void
     */
    public static function default():void
    {
        static::setFindBestMask();
        static::forceMode(-1);
        static::allowCache('');
    }
    
//region mask settings
    /** @var bool $FindBestMask if true, estimates best mask (spec. default, but extremelly slow; set to false to significant performance boost but (propably) worst quality code */
    public static bool $FindBestMask = true;
    /** @var bool $FindMaskFromRandom if false, checks all masks available, otherwise see {@see QRSettings::MaskCount}, mask id are got randomly */
    public static bool $FindMaskFromRandom = false;
    /** @var int $MaskCount when {@see QRSettings::FindMaskFromRandom} is true, this value tells count of masks need to be checked, mask id are got randomly */
    public static int $MaskCount=5;
    /** @var int $DefaultMask Set default mask, value 0 to 7, only used when {@see QRSettings::FindBestMask} is false */
    public static int $DefaultMask=2;
    
    /**
     * Set settings to find best mask.
     *
     * This setting is slower but produce better quality, see {@see QRSettings::setDefaultMask()} for faster mask setting.
     * @param int|null $maskCount if null, check all possibles masks, otherwise find $maskCount masks from random.
     * @return void
     */
    public static function setFindBestMask(?int $maskCount = null):void
    {
        static::$FindBestMask = true;
        if(is_null($maskCount))
        {
            static::$FindMaskFromRandom = false;
        }
        else
        {
            static::$MaskCount          = $maskCount;
            static::$FindMaskFromRandom = true;
        }
    }
    
    /** Do not find best mask.
     * This setting is faster but produce worst quality, see {@see QRSettings::setFindBestMask()} for other option.
     * @param int $mask mask number, value 0 to 7
     * @return void
     */
    public static function setDefaultMask(int $mask):void
    {
        static::$FindBestMask = false;
        static::$DefaultMask = $mask % 8;
    }
//endregion mask settings

//region mode
    /**
     * Forced mode :
     * * {@see QRConstants::QR_MODE_NUM} : Force Numeric Mode
     * * {@see QRConstants::QR_MODE_AN} : Force Alpha-Numeric Mode
     * * {@see QRConstants::QR_MODE_8} : Force BYTE8 Mode (utf8)
     * * {@see QRConstants::QR_MODE_KANJI} : Force Kanji Mode (Shift-JIS)
     * @param int $mode -1:automatic, otherwise:force one of these mode.
     * @return void
     */
    public static function forceMode(int $mode):void
    {
        static::$forcedMode=$mode;
    }

    protected static int $forcedMode = -1;
    public static function isForcedMode():bool
    {
        return static::$forcedMode !== -1;
    }
    
    public static function getForcedMode():int
    {
        return static::$forcedMode;
    }
//endregion mode

//region cache
    /** Allow cache, faster QR code generation in case of multiple QR to build.
     * @param string $cacheFolder provide a folder path to store cache files, empty string to disable cache.
     * @return void
     */
    public static function allowCache(string $cacheFolder):void
    {
        static::$allowCache = $cacheFolder != '';
        static::$cacheFolder = str_ends_with($cacheFolder,DIRECTORY_SEPARATOR) ? $cacheFolder : $cacheFolder.DIRECTORY_SEPARATOR;
        
    }
    protected static bool $allowCache = false;
    protected static string $cacheFolder = '';
    public static function isCacheActive():bool
    {
        return static::$allowCache;
    }
    public static function getCacheDir():string
    {
        return static::$cacheFolder;
    }
//endregion cache


}