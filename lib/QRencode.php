<?php
/*
 * PHP QR Code encoder
 *
 * Main encoder classes.
 *
 * Based on libqrencode C library distributed under LGPL 2.1
 * Copyright (C) 2006, 2007, 2008, 2009 Kentaro Fukuchi <fukuchi@megaui.net>
 *
 * PHP QR Code is distributed under LGPL 3
 * Copyright (C) 2010 Dominik Dzienia <deltalab at poczta dot fm>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */

namespace primer\phpqrcode;


use Exception;

class QRencode
{
    
    public $casesensitive = true;
    
    public $version = 0;
    public $size    = 3;
    public $margin  = 4;
    
    /** @var int $level error correction level 0 to 3 */
    public $level = QRConstants::QR_ECLEVEL_L;
    public $hint  = QRConstants::QR_MODE_8;
    
    protected function isEightBit():bool
    {
        return QRSettings::isForcedMode() &&
            ( QRSettings::getForcedMode() == QRConstants::QR_MODE_8
              || QRSettings::getForcedMode() == QRConstants::QR_MODE_KANJI);
    }
    
    /**
     * @param int $level Error correction level
     * @param int $size pixel size
     * @param int $margin silent zone size
     * @return QRencode
     */
    public static function factory(int $level = QRConstants::QR_ECLEVEL_L, int $size = 3, int $margin = 4):QRencode
    {
        $enc         = new QRencode();
        $enc->size   = $size;
        $enc->margin = $margin;
        
        switch($level.'')
        {
            case '0':
            case '1':
            case '2':
            case '3':
                $enc->level = $level;
                break;
            default:
                $enc->level = QRConstants::QR_ECLEVEL_L;
                break;
        }
        
        return $enc;
    }
    
    //----------------------------------------------------------------------
    public function encodeRAW($intext, $outfile = false)
    {
        $code = new QRcode();
        
        if($this->isEightBit())
        {
            $code->encodeString8bit($intext, $this->version, $this->level);
        }
        else
        {
            $code->encodeString($intext, $this->version, $this->level, $this->hint, $this->casesensitive);
        }
        
        return $code->data;
    }
    
    /**
     * @param string $intext
     * @param bool   $outfile
     * @return mixed|void
     * @throws Exception
     */
    public function encode(string $intext, bool $outfile = false)
    {
        $code = new QRcode();
        
        if($this->isEightBit())
        {
            $code->encodeString8bit($intext, $this->version, $this->level);
        }
        else
        {
            $code->encodeString($intext, $this->version, $this->level, $this->hint, $this->casesensitive);
        }
        
        if($outfile !== false)
        {
            file_put_contents($outfile, join("\n", QRtools::binarize($code->data)));
        }
        else
        {
            return QRtools::binarize($code->data);
        }
    }
    
    //----------------------------------------------------------------------
    public function encodePNG(string $intext, ?string $outfile = null, bool $sendToBrowser = false):void
    {
        try
        {
            
            ob_start();
            $tab = $this->encode($intext); //todo it may raise an Exception here (empty string) then ob_end_clean() is not proceeded. we have to fix that.
            $err = ob_get_contents();
            ob_end_clean();
            
            if($err != '')
                QRtools::log($outfile, $err);
            
            $maxSize = (int)(QRConstants::QR_PNG_MAXIMUM_SIZE/(count($tab) + 2*$this->margin));
            
            QRimage::png($tab, $outfile, min(max(1, $this->size), $maxSize), $this->margin, $sendToBrowser);
            
        } catch(Exception $e)
        {
            
            QRtools::log($outfile, $e->getMessage());
            
        }
    }
}
