<?php

namespace primer\phpqrcode;

use Exception;
use primer\phpqrcode\Internal\FrameFiller;
use primer\phpqrcode\Internal\QRencode;
use primer\phpqrcode\Internal\QRinput;
use primer\phpqrcode\Internal\QRmask;
use primer\phpqrcode\Internal\QRrawcode;
use primer\phpqrcode\Internal\QRspec;
use primer\phpqrcode\Internal\QRsplit;

class QRcode
{
    public int $version;
    public int $width;
    /** @var array<array<int>> $data */
    public array $data;
    
    /**
     * @param QRinput $input
     * @param int     $maskNo
     * @return $this|null --todo null possible ?
     * @throws Exception
     */
    public function encodeMask(QRinput $input, int $maskNo):?self
    {
        if($input->getVersion() < 0 || $input->getVersion() > QRConstants::QRSPEC_VERSION_MAX)
        {
            throw new Exception('wrong version');
        }
        if($input->getErrorCorrectionLevel() > QRConstants::QR_ECLEVEL_H)
        {
            throw new Exception('wrong level');
        }
        
        $raw = new QRrawcode($input);
        
        $version = $raw->version;
        $width   = QRspec::getWidth($version);
        $frame   = QRspec::newFrame($version);
        
        $filler = new FrameFiller($width, $frame);
        if(is_null($filler)) //todo check possible case ?
        {
            return null;
        }
        
        // inteleaved data and ecc codes
        for($i = 0; $i < $raw->dataLength + $raw->eccLength; $i++)
        {
            $code = $raw->getCode();
            $bit  = 0x80;
            for($j = 0; $j < 8; $j++)
            {
                $addr = $filler->next();
                $filler->setFrameAt($addr, 0x02 | (($bit & $code) != 0));
                $bit = $bit >> 1;
            }
        }
        
        unset($raw);
        
        // remainder bits
        $j = QRspec::getRemainder($version);
        for($i = 0; $i < $j; $i++)
        {
            $addr = $filler->next();
            $filler->setFrameAt($addr, 0x02);
        }
        
        $frame = $filler->frame;
        unset($filler);
        
        
        // masking
        $maskObj = new QRmask();
        if($maskNo < 0)
        {
            
            if(QRSettings::$FindBestMask)
            {
                $masked = $maskObj->mask($width, $frame, $input->getErrorCorrectionLevel());
            }
            else
            {
                $masked = $maskObj->makeMask($width, $frame, (intval(QRSettings::$DefaultMask)%8), $input->getErrorCorrectionLevel());
            }
        }
        else
        {
            $masked = $maskObj->makeMask($width, $frame, $maskNo, $input->getErrorCorrectionLevel());
        }
        
        if($masked == null) // possible ? todo check
        {
            return null;
        }
        
        $this->version = $version;
        $this->width   = $width;
        $this->data    = $masked;
        
        return $this;
    }
    
    /**
     * @param QRinput $input
     * @return $this|null --todo null possible ?
     * @throws Exception
     */
    public function encodeInput(QRinput $input):?self
    {
        return $this->encodeMask($input, -1);
    }
    
    /**
     * @param string|null $string
     * @param int         $version
     * @param int         $level
     * @return $this|null
     * @throws Exception
     */
    public function encodeString8bit(?string $string, int $version, int $level):?self
    {
        if($string == null)
        {
            throw new Exception('empty string!');
        }
        
        $input = new QRinput($version, $level);
        
        $ret = $input->append( QRConstants::QR_MODE_8, strlen($string),str_split($string));
        if($ret < 0)
        {
            unset($input);
            return null;
        }
        return $this->encodeInput($input);
    }
    
    /**
     * @param $string
     * @param int $version
     * @param int $level
     * @param int $hint
     * @param bool $casesensitive
     * @return $this|null
     * @throws Exception
     */
    public function encodeString($string, int $version, int $level, int $hint, bool $casesensitive):?self
    {
        
        if($hint != QRConstants::QR_MODE_8 && $hint != QRConstants::QR_MODE_KANJI)
        {
            throw new Exception('bad hint');
        }
        
        $input = new QRinput($version, $level);
        
        $ret = QRsplit::splitStringToQRinput($string, $input, $hint, $casesensitive);
        if($ret < 0) // todo check possible case ?
        {
            return null;
        }
        
        return $this->encodeInput($input);
    }
    
    //----------------------------------------------------------------------
    
    /**
     * @param string  $text          Text data to encode
     * @param ?string $outfile       Png file to create, (can be null when false when sendToBrowser==true)
     * @param int     $level         Correction level {@see QRConstants::QR_ECLEVEL_L}, {@see QRConstants::QR_ECLEVEL_M}, {@see QRConstants::QR_ECLEVEL_Q}, {@see QRConstants::QR_ECLEVEL_H}
     * @param int     $size          Pixel size
     * @param int     $margin        Margin (silent zone)
     * @param bool    $sendToBrowser Png to be sent to browser.
     * @return void
     */
    public static function png(string $text, ?string $outfile = null, int $level = QRConstants::QR_ECLEVEL_L, int $size = 3, int $margin = 4, bool $sendToBrowser = false):void
    {
        $enc = QRencode::factory($level, $size, $margin);
        $enc->encodePNG($text, $outfile, $sendToBrowser);
    }
    
    //----------------------------------------------------------------------
    public static function text($text, ?string $outfile = null, int $level = QRConstants::QR_ECLEVEL_L, int $size = 3, int $margin = 4)
    {
        $enc = QRencode::factory($level, $size, $margin);
        return $enc->encode($text, $outfile);
    }
    
    //----------------------------------------------------------------------
    public static function raw($text, ?string $outfile = null, int $level = QRConstants::QR_ECLEVEL_L, int $size = 3, int $margin = 4)
    {
        $enc = QRencode::factory($level, $size, $margin);
        return $enc->encodeRAW($text, $outfile);
    }
}