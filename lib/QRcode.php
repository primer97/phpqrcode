<?php

namespace primer\phpqrcode;

use Exception;

class QRcode
{
    
    public $version;
    public $width;
    public $data;
    
    //----------------------------------------------------------------------
    public function encodeMask(QRinput $input, $mask)
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
        if(is_null($filler))
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
        if($mask < 0)
        {
            
            if(QRConstants::QR_FIND_BEST_MASK)
            {
                $masked = $maskObj->mask($width, $frame, $input->getErrorCorrectionLevel());
            }
            else
            {
                $masked = $maskObj->makeMask($width, $frame, (intval(QRConstants::QR_DEFAULT_MASK)%8), $input->getErrorCorrectionLevel());
            }
        }
        else
        {
            $masked = $maskObj->makeMask($width, $frame, $mask, $input->getErrorCorrectionLevel());
        }
        
        if($masked == null)
        {
            return null;
        }
        
        $this->version = $version;
        $this->width   = $width;
        $this->data    = $masked;
        
        return $this;
    }
    
    //----------------------------------------------------------------------
    public function encodeInput(QRinput $input)
    {
        return $this->encodeMask($input, -1);
    }
    
    //----------------------------------------------------------------------
    public function encodeString8bit($string, $version, $level)
    {
        if($string == null)
        {
            throw new Exception('empty string!');
        }
        
        $input = new QRinput($version, $level);
        if($input == null) return null;
        
        $ret = $input->append($input, QRConstants::QR_MODE_8, strlen($string));
        if($ret < 0)
        {
            unset($input);
            return null;
        }
        return $this->encodeInput($input);
    }
    
    //----------------------------------------------------------------------
    public function encodeString($string, $version, $level, $hint, $casesensitive)
    {
        
        if($hint != QRConstants::QR_MODE_8 && $hint != QRConstants::QR_MODE_KANJI)
        {
            throw new Exception('bad hint');
        }
        
        $input = new QRinput($version, $level);
        if($input == null) return null;
        
        $ret = QRsplit::splitStringToQRinput($string, $input, $hint, $casesensitive);
        if($ret < 0)
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
    public static function text($text, $outfile = false, $level = QRConstants::QR_ECLEVEL_L, $size = 3, $margin = 4)
    {
        $enc = QRencode::factory($level, $size, $margin);
        return $enc->encode($text, $outfile);
    }
    
    //----------------------------------------------------------------------
    public static function raw($text, $outfile = false, $level = QRConstants::QR_ECLEVEL_L, $size = 3, $margin = 4)
    {
        $enc = QRencode::factory($level, $size, $margin);
        return $enc->encodeRAW($text, $outfile);
    }
}