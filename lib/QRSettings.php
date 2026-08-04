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
     * Reset to default settings : {@see QRSettings::setFindBestMask() Best Mask}
     * @return void
     */
    public static function default()
    {
        static::setFindBestMask();
    }
    
//region mask settings
    /** @var bool $FindBestMask if true, estimates best mask (spec. default, but extremally slow; set to false to significant performance boost but (propably) worst quality code */
    public static $FindBestMask = true;
    /** @var bool $FindMaskFromRandom if false, checks all masks available, otherwise see {@see QRSettings::MaskCount}, mask id are got randomly */
    public static $FindMaskFromRandom = false;
    /** @var int $MaskCount when {@see QRSettings::FindMaskFromRandom} is true, this value tells count of masks need to be checked, mask id are got randomly */
    public static $MaskCount=5;
    /** @var int $DefaultMask Set default mask, value 0 to 7, only used when {@see QRSettings::FindBestMask} is false */
    public static $DefaultMask=2;
    
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

}