<?php

namespace primer\phpqrcode\Internal;


use Exception;
use primer\phpqrcode\QRConstants;

/**
 * @internal
 */
class QRinput
{
    /** @var array<QRinputItem> $items */
    public $items;
    
    /** @var int $version */
    private $version;
    /** @var int $level */
    private $level;
    
    //----------------------------------------------------------------------
    public function __construct(int $version = 0, int $level = QRConstants::QR_ECLEVEL_L)
    {
        if($version < 0 || $version > QRConstants::QRSPEC_VERSION_MAX || $level > QRConstants::QR_ECLEVEL_H)
        {
            throw new Exception('Invalid version no');
        }
        
        $this->version = $version;
        $this->level   = $level;
    }
    
    //----------------------------------------------------------------------
    public function getVersion():int
    {
        return $this->version;
    }
    
    //----------------------------------------------------------------------
    private function setVersion(int $version):void
    {
        if($version < 0 || $version > QRConstants::QRSPEC_VERSION_MAX)
        {
            throw new Exception('Invalid version no');
        }
        
        $this->version = $version;
    }
    
    //----------------------------------------------------------------------
    public function getErrorCorrectionLevel():int
    {
        return $this->level;
    }
    
    //----------------------------------------------------------------------
    public function setErrorCorrectionLevel(int $level):int
    {
        if($level > QRConstants::QR_ECLEVEL_H)
        {
            throw new Exception('Invalid ECLEVEL');
        }
        
        $this->level = $level;
        
        return 0;
    }
    
    //----------------------------------------------------------------------
    private function appendEntry(QRinputItem $entry):void
    {
        $this->items[] = $entry;
    }
    
    
    /**
     * @param int $mode
     * @param int $size
     * @param array<string> $data
     * @return int
     */
    public function append(int $mode, int $size, array $data):int
    {
        try
        {
            $entry         = new QRinputItem($mode, $size, $data);
            $this->items[] = $entry;
            return 0;
        } catch(Exception $e)
        {
            return -1;
        }
    }
    
    //----------------------------------------------------------------------
    
    private function insertStructuredAppendHeader(int $size, int $index, int $parity):int
    {
        if($size > QRConstants::MAX_STRUCTURED_SYMBOLS)
        {
            throw new Exception('insertStructuredAppendHeader wrong size');
        }
        
        if($index <= 0 || $index > QRConstants::MAX_STRUCTURED_SYMBOLS)
        {
            throw new Exception('insertStructuredAppendHeader wrong index');
        }
        
        $buf = [$size, $index, $parity];
        
        try
        {
            $entry = new QRinputItem(QRConstants::QR_MODE_STRUCTURE, 3, $buf);
            array_unshift($this->items, $entry);
            return 0;
        } catch(Exception $e)
        {
            return -1;
        }
    }
    
    //----------------------------------------------------------------------
    private function calcParity()
    {
        $parity = 0;
        
        foreach($this->items as $item)
        {
            if($item->mode != QRConstants::QR_MODE_STRUCTURE)
            {
                for($i = $item->size - 1; $i >= 0; $i--)
                {
                    $parity ^= $item->data[$i];
                }
            }
        }
        
        return $parity;
    }
    
    
    /**
     * @param int           $size
     * @param array<string> $data
     * @return bool
     */
    private static function checkModeNum(int $size, array $data):bool
    {
        for($i = 0; $i < $size; $i++)
        {
            if((ord($data[$i]) < ord('0')) || (ord($data[$i]) > ord('9')))
            {
                return false;
            }
        }
        
        return true;
    }
    
    //----------------------------------------------------------------------
    public static function estimateBitsModeNum(int $size):int
    {
        $w    = (int)$size/3;
        $bits = $w*10;
        
        switch($size - $w*3)
        {
            case 1:
                $bits += 4;
                break;
            case 2:
                $bits += 7;
                break;
            default:
                break;
        }
        
        return $bits;
    }
    
    /**
     * @var array<int> $anTable
     */
    protected static $anTable = [
        -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
        -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
        36, -1, -1, -1, 37, 38, -1, -1, -1, -1, 39, 40, -1, 41, 42, 43,
        0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 44, -1, -1, -1, -1, -1,
        -1, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24,
        25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, -1, -1, -1, -1, -1,
        -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
        -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1
    ];
    
    //----------------------------------------------------------------------
    public static function lookAnTable(int $c):int
    {
        return (($c > 127) ? -1 : self::$anTable[$c]);
    }
    
    /**
     * @param int           $size
     * @param array<string> $data
     * @return bool
     */
    private static function checkModeAn(int $size, array $data):bool
    {
        for($i = 0; $i < $size; $i++)
        {
            if(self::lookAnTable(ord($data[$i])) == -1)
            {
                return false;
            }
        }
        
        return true;
    }
    
    //----------------------------------------------------------------------
    public static function estimateBitsModeAn(int $size):int
    {
        $w    = (int)($size/2);
        $bits = $w*11;
        
        if($size & 1)
        {
            $bits += 6;
        }
        
        return $bits;
    }
    
    //----------------------------------------------------------------------
    public static function estimateBitsMode8(int $size):int
    {
        return $size*8;
    }
    
    //----------------------------------------------------------------------
    public static function estimateBitsModeKanji(int $size):int
    {
        return (int)(($size/2)*13);
    }
    
    /**
     * @param int           $size
     * @param array<string> $data
     * @return bool
     */
    private static function checkModeKanji(int $size, array $data):bool
    {
        if($size & 1)
            return false;
        
        for($i = 0; $i < $size; $i += 2)
        {
            $val = (ord($data[$i]) << 8) | ord($data[$i + 1]);
            if($val < 0x8140
                || ($val > 0x9ffc && $val < 0xe040)
                || $val > 0xebbf)
            {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Validation
     * @param int   $mode
     * @param int   $size
     * @param array<string> $data
     * @return bool
     */
    public static function check(int $mode, int $size, array $data):bool
    {
        if($size <= 0)
            return false;
        
        switch($mode)
        {
            case QRConstants::QR_MODE_NUM:
                return self::checkModeNum($size, $data);
            case QRConstants::QR_MODE_AN:
                return self::checkModeAn($size, $data);
            case QRConstants::QR_MODE_KANJI:
                return self::checkModeKanji($size, $data);
            case QRConstants::QR_MODE_8:
                return true;
            case QRConstants::QR_MODE_STRUCTURE:
                return true;
            
            default:
                break;
        }
        
        return false;
    }
    
    
    //----------------------------------------------------------------------
    private function estimateBitStreamSize(int $version):int
    {
        $bits = 0;
        
        foreach($this->items as $item)
        {
            $bits += $item->estimateBitStreamSizeOfEntry($version);
        }
        
        return $bits;
    }
    
    //----------------------------------------------------------------------
    private function estimateVersion():int
    {
        $version = 0;
        $prev    = 0;
        do
        {
            $prev    = $version;
            $bits    = $this->estimateBitStreamSize($prev);
            $version = QRspec::getMinimumVersion((int)(($bits + 7)/8), $this->level);
            if($version < 0)
            {
                return -1;
            }
        } while($version > $prev);
        
        return $version;
    }
    
    //----------------------------------------------------------------------
    private static function lengthOfCode(int $mode, int $version, int $bits):int
    {
        $payload = $bits - 4 - QRspec::lengthIndicator($mode, $version);
        switch($mode)
        {
            case QRConstants::QR_MODE_NUM:
                $chunks = (int)($payload/10);
                $remain = $payload - $chunks*10;
                $size   = $chunks*3;
                if($remain >= 7)
                {
                    $size += 2;
                }
                else if($remain >= 4)
                {
                    $size += 1;
                }
                break;
            case QRConstants::QR_MODE_AN:
                $chunks = (int)($payload/11);
                $remain = $payload - $chunks*11;
                $size   = $chunks*2;
                if($remain >= 6)
                    $size++;
                break;
            case QRConstants::QR_MODE_8:
                $size = (int)($payload/8);
                break;
            case QRConstants::QR_MODE_KANJI:
                $size = (int)(($payload/13)*2);
                break;
            case QRConstants::QR_MODE_STRUCTURE:
                $size = (int)($payload/8);
                break;
            default:
                $size = 0;
                break;
        }
        
        $maxsize = QRspec::maximumWords($mode, $version);
        if($size < 0) $size = 0;
        if($size > $maxsize) $size = $maxsize;
        
        return $size;
    }
    
    //----------------------------------------------------------------------
    private function createBitStream():int
    {
        $total = 0;
        
        foreach($this->items as $item)
        {
            $bits = $item->encodeBitStream($this->version);
            
            if($bits < 0)
                return -1;
            
            $total += $bits;
        }
        
        return $total;
    }
    
    //----------------------------------------------------------------------
    private function convertData():int
    {
        $ver = $this->estimateVersion();
        if($ver > $this->getVersion())
        {
            $this->setVersion($ver);
        }
        
        for(; ;)
        {
            $bits = $this->createBitStream();
            
            if($bits < 0)
                return -1;
            
            $ver = QRspec::getMinimumVersion((int)(($bits + 7)/8), $this->level);
            if($ver < 0)
            {
                throw new Exception('WRONG VERSION');
            }
            else if($ver > $this->getVersion())
            {
                $this->setVersion($ver);
            }
            else
            {
                break;
            }
        }
        
        return 0;
    }
    
    //----------------------------------------------------------------------
    private function appendPaddingBit(QRbitstream &$bstream):int
    {
        $bits     = $bstream->size();
        $maxwords = QRspec::getDataLength($this->version, $this->level);
        $maxbits  = $maxwords*8;
        
        if($maxbits == $bits)
        {
            return 0;
        }
        
        if($maxbits - $bits < 5)
        {
            return $bstream->appendNum($maxbits - $bits, 0);
        }
        
        $bits  += 4;
        $words = (int)(($bits + 7)/8);
        
        $padding = new QRbitstream();
        $ret     = $padding->appendNum($words*8 - $bits + 4, 0);
        
        if($ret < 0)
            return $ret;
        
        $padlen = $maxwords - $words;
        
        if($padlen > 0)
        {
            
            $padbuf = [];
            for($i = 0; $i < $padlen; $i++)
            {
                $padbuf[$i] = ($i & 1) ? 0x11 : 0xec;
            }
            
            $ret = $padding->appendBytes($padlen, $padbuf);
            
            if($ret < 0)
                return $ret;
            
        }
        
        $ret = $bstream->append($padding);
        
        return $ret;
    }
    
    /**
     * @return QRbitstream|null
     * @throws Exception
     */
    private function mergeBitStream()
    {
        if($this->convertData() < 0)
        {
            return null;
        }
        
        $bstream = new QRbitstream();
        
        foreach($this->items as $item)
        {
            $ret = $bstream->append($item->bstream);
            if($ret < 0)
            {
                return null;
            }
        }
        
        return $bstream;
    }
    
    /**
     * @return QRbitstream|null
     */
    private function getBitStream()
    {
        
        $bstream = $this->mergeBitStream();
        
        if($bstream == null)
        {
            return null;
        }
        
        $ret = $this->appendPaddingBit($bstream);
        if($ret < 0)
        {
            return null;
        }
        
        return $bstream;
    }
    
    /**
     * @return array<int>|null
     */
    public function getByteStream():?array
    {
        $bstream = $this->getBitStream();
        if(is_null($bstream))
        {
            return null;
        }
        
        return $bstream->toByte();
    }
}